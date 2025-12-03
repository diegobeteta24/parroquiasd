<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateIntentionCertificate;
use App\Models\Intention;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Svix\Webhook;
use Svix\Exception\WebhookVerificationException;

class RecurrenteWebhookController extends Controller
{
    /**
     * Maneja los webhooks de Recurrente verificados por Svix.
     * 
     * Eventos procesados:
     * - payment_intent.succeeded: crea una nueva intención
     * - payment_intent.failed: solo registro en logs
     * - refund.create: marcar intención como reembolsada
     */
    public function handle(Request $request)
    {
        Log::info('🚀 [WEBHOOK INICIO] Request recibido', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $primarySecret = config('services.svix.webhook_secret') ?? env('SVIX_WEBHOOK_SECRET');
        $extraSecrets = config('services.svix.webhook_secrets', []);
        if (is_string($extraSecrets) && $extraSecrets !== '') {
            $extraSecrets = [$extraSecrets];
        } elseif (!is_array($extraSecrets)) {
            $extraSecrets = [];
        }

        $candidateSecrets = array_values(array_unique(array_filter(array_merge(
            $primarySecret ? [$primarySecret] : [],
            $extraSecrets
        ))));

        if (empty($candidateSecrets)) {
            Log::error('❌ [WEBHOOK ERROR] SVIX_WEBHOOK_SECRET no configurado');
            return response('Configuration error', 500);
        }

        Log::info('✅ [WEBHOOK] Secret(s) configurado(s)', [
            'count' => count($candidateSecrets),
            'secret_prefixes' => array_map(function ($secret) {
                return substr($secret, 0, 15) . '...';
            }, $candidateSecrets),
        ]);

        // Obtener contenido raw y headers de Svix
        $raw = $request->getContent();
        $headers = [
            'svix-id'        => $request->header('svix-id'),
            'svix-timestamp' => $request->header('svix-timestamp'),
            'svix-signature' => $request->header('svix-signature'),
        ];

        // Log COMPLETO del webhook para debugging
        Log::info('🔔 [WEBHOOK] Payload recibido', [
            'ip' => $request->ip(),
            'headers' => $headers,
            'raw_payload' => $raw,
            'content_length' => strlen($raw),
            'all_headers' => $request->headers->all(),
        ]);

        // Verificar firma Svix
        Log::info('🔐 [WEBHOOK] Verificando firma Svix...');
        $verified = false;
        $matchedSecret = null;
        $errors = [];

        foreach ($candidateSecrets as $secret) {
            try {
                (new Webhook($secret))->verify($raw, $headers);
                $verified = true;
                $matchedSecret = $secret;
                break;
            } catch (WebhookVerificationException $e) {
                $errors[] = [
                    'secret_prefix' => substr($secret, 0, 15) . '...',
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (!$verified) {
            Log::error('❌ [WEBHOOK] Firma inválida para todos los secretos configurados', [
                'ip' => $request->ip(),
                'headers_received' => $headers,
                'errors' => $errors,
            ]);
            return response('Invalid signature', 400);
        }

        Log::info('✅ [WEBHOOK] Firma Svix verificada correctamente', [
            'secret_prefix' => substr($matchedSecret, 0, 15) . '...',
        ]);

        // Decodificar payload
        Log::info('📦 [WEBHOOK] Decodificando payload...');
        $payload = json_decode($raw, true);
        
        if (!$payload) {
            Log::error('❌ [WEBHOOK] Error al decodificar JSON', [
                'json_error' => json_last_error_msg(),
                'raw' => substr($raw, 0, 500),
            ]);
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        $topLevelMetadata = $payload['metadata'] ?? [];
        if (!is_array($topLevelMetadata)) {
            $topLevelMetadata = [];
        }

        $checkoutMetadata = data_get($payload, 'checkout.metadata', []);
        if (!is_array($checkoutMetadata)) {
            $checkoutMetadata = [];
        }

        // Checkout metadata es donde definimos los datos de la intención, por lo que lo usamos como base.
        $metadata = array_merge($checkoutMetadata, $topLevelMetadata);

        $eventType = $payload['event_type'] ?? null;

        Log::info('📋 [WEBHOOK] Evento decodificado', [
            'event_type' => $eventType,
            'payment_id' => $payload['id'] ?? null,
            'amount_in_cents' => $payload['amount_in_cents'] ?? null,
            'currency' => $payload['currency'] ?? null,
            'metadata_keys' => array_keys($metadata),
        ]);

        // Procesar según tipo de evento
        switch ($eventType) {
            case 'payment_intent.succeeded':
                Log::info('💰 [WEBHOOK] Procesando pago exitoso...');
                return $this->handlePaymentSucceeded($payload);

            case 'payment_intent.failed':
                Log::info('❌ [WEBHOOK] Procesando pago fallido...');
                return $this->handlePaymentFailed($payload);

            case 'refund.create':
                Log::info('💸 [WEBHOOK] Procesando reembolso...');
                return $this->handleRefundCreated($payload);

            default:
                Log::warning('⚠️ [WEBHOOK] Evento ignorado', ['event_type' => $eventType]);
                return response()->json(['ignored' => true]);
        }
    }

    /**
     * Procesa un pago exitoso y crea la intención en BD.
     */
    protected function handlePaymentSucceeded(array $payload)
    {
        Log::info('💳 [PAYMENT] Extrayendo datos del payload...');
        
        $paymentId = $payload['id'] ?? null;
        $amountCents = (int)($payload['amount_in_cents'] ?? 0);
        $currency = $payload['currency'] ?? 'GTQ';
        $topLevelMetadata = $payload['metadata'] ?? [];
        if (!is_array($topLevelMetadata)) {
            $topLevelMetadata = [];
        }

        $checkoutMetadata = data_get($payload, 'checkout.metadata', []);
        if (!is_array($checkoutMetadata)) {
            $checkoutMetadata = [];
        }

        // Mezclamos metadata del payload raíz y del checkout (checkout prevalece cuando falte la raíz).
        $metadata = array_merge($checkoutMetadata, $topLevelMetadata);
        
        Log::info('📊 [PAYMENT] Datos extraídos', [
            'payment_id' => $paymentId,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'metadata' => $metadata,
        ]);
        
        // Parsear fecha de pago
        $paidAt = isset($payload['created_at']) 
            ? Carbon::parse($payload['created_at']) 
            : now();

        // Validaciones básicas
        if (!$paymentId || $amountCents <= 0) {
            Log::error('❌ [PAYMENT] Validación fallida', [
                'payment_id' => $paymentId,
                'amount_cents' => $amountCents,
            ]);
            return response()->json(['error' => 'Invalid payment data'], 400);
        }

        Log::info('✅ [PAYMENT] Validaciones básicas pasadas');

        // Idempotencia: verificar si ya existe
        Log::info('🔍 [PAYMENT] Verificando idempotencia...');
        $existing = Intention::where('payment_intent_id', $paymentId)->first();
        if ($existing) {
            Log::warning('⚠️ [PAYMENT] Pago ya procesado (idempotencia)', [
                'payment_id' => $paymentId,
                'intention_id' => $existing->id,
            ]);
            return response()->json(['ok' => true, 'already_exists' => true]);
        }
        
        Log::info('✅ [PAYMENT] Pago no existe, procediendo a crear...');

        // Crear la intención en una transacción
        Log::info('🔄 [DB] Iniciando transacción...');
        try {
            $customer = $payload['customer'] ?? [];
            if (!is_array($customer)) {
                $customer = [];
            }

            $intention = DB::transaction(function () use ($paymentId, $amountCents, $currency, $paidAt, $metadata, $payload, $customer) {
                Log::info('🔍 [DB] Buscando mass_instance_id...');
                
                // Si no hay mass_id, buscar la próxima misa disponible
                $massInstanceId = $metadata['mass_id'] ?? null;
                if (is_string($massInstanceId)) {
                    $massInstanceId = trim($massInstanceId);
                }
                if ($massInstanceId === '' || !is_numeric($massInstanceId)) {
                    $massInstanceId = null;
                } else {
                    $massInstanceId = (int) $massInstanceId;
                }
                
                Log::info('📋 [DB] mass_id en metadata', ['mass_id' => $massInstanceId]);
                
                if (!$massInstanceId) {
                    Log::info('🔍 [DB] No hay mass_id, buscando próxima misa...');
                    
                    // Buscar la próxima misa disponible (con fecha futura)
                    $nextMass = \App\Models\MassInstance::where('starts_at', '>', now())
                        ->orderBy('starts_at', 'asc')
                        ->first();
                    
                    if ($nextMass) {
                        $massInstanceId = $nextMass->id;
                        Log::info('✅ [DB] Misa futura encontrada', [
                            'mass_id' => $massInstanceId,
                            'starts_at' => $nextMass->starts_at,
                        ]);
                    } else {
                        Log::warning('⚠️ [DB] No hay misas futuras, buscando la más reciente...');
                        
                        // Si no hay misa futura, usar la más reciente
                        $latestMass = \App\Models\MassInstance::latest('starts_at')->first();
                        if ($latestMass) {
                            $massInstanceId = $latestMass->id;
                            Log::info('✅ [DB] Usando misa más reciente', [
                                'mass_id' => $massInstanceId,
                                'starts_at' => $latestMass->starts_at,
                            ]);
                        } else {
                            Log::error('❌ [DB] No hay misas en el sistema');
                            throw new \RuntimeException('No hay misas disponibles en el sistema');
                        }
                    }
                }
                
                $intentionData = [
                    // Datos de Recurrente
                    'payment_intent_id' => $paymentId,
                    'amount_in_cents'   => $amountCents,
                    'currency'          => $currency,
                    'metadata'          => $payload, // Guardar webhook completo
                    
                    // Datos de la intención desde metadata
                    'mass_instance_id'  => $massInstanceId,
                    'type'              => $metadata['intention_type'] ?? 'normal',
                    'category'          => $metadata['category'] ?? null,
                    'public_text'       => $metadata['public_text'] ?? null,
                    
                    // Datos del donante
                    'donor_name'        => $metadata['donor_name'] ?? ($customer['full_name'] ?? null),
                    'email'             => $metadata['donor_email'] ?? ($customer['email'] ?? null),
                    'phone'             => $metadata['donor_phone'] ?? null,
                    
                    // Estado del pago
                    'status'            => 'paid',
                    'paid_at'           => $paidAt,
                    'payment_method'    => 'recurrente',
                    'payment_ref'       => $paymentId,
                    'amount'            => $amountCents / 100, // Convertir a GTQ
                    'channel'           => 'online',
                    'code'              => strtoupper(bin2hex(random_bytes(4))),
                    'certificate_token' => Str::random(64),
                ];
                
                Log::info('💾 [DB] Creando intención con datos', $intentionData);
                
                $intention = Intention::create($intentionData);

                $dedicateeName = $metadata['dedicatee_name'] ?? $metadata['dedicatee'] ?? null;
                if (is_string($dedicateeName)) {
                    $dedicateeName = trim($dedicateeName);
                } else {
                    $dedicateeName = null;
                }

                if ($dedicateeName) {
                    $intention->dedicatee()->updateOrCreate([], ['name' => $dedicateeName]);
                }
                
                Log::info('✅ [DB] Intención creada exitosamente', [
                    'intention_id' => $intention->id,
                ]);
                
                return $intention;
            });

            Log::info('🎉 [SUCCESS] Intención creada exitosamente', [
                'payment_id' => $paymentId,
                'intention_id' => $intention->id,
                'amount_cents' => $amountCents,
            ]);

            GenerateIntentionCertificate::dispatchSync($intention->id);

            return response()->json([
                'ok' => true,
                'intention_id' => $intention->id,
            ]);

        } catch (\Exception $e) {
            Log::error('💥 [ERROR] Error al crear intención', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to create intention',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registra un pago fallido (solo logging).
     */
    protected function handlePaymentFailed(array $payload)
    {
        $paymentId = $payload['id'] ?? null;
        $failureReason = $payload['failure_message'] ?? 'Unknown';

        Log::warning('Webhook Recurrente: pago fallido', [
            'payment_id' => $paymentId,
            'reason' => $failureReason,
            'metadata' => $payload['metadata'] ?? [],
        ]);

        return response()->json(['ok' => true, 'logged' => true]);
    }

    /**
     * Marca una intención como reembolsada.
     */
    protected function handleRefundCreated(array $payload)
    {
        $paymentId = $payload['payment_intent_id'] ?? null;

        if (!$paymentId) {
            return response()->json(['error' => 'Missing payment_intent_id'], 400);
        }

        $intention = Intention::where('payment_intent_id', $paymentId)->first();

        if (!$intention) {
            Log::warning('Webhook Recurrente: reembolso para intención inexistente', [
                'payment_id' => $paymentId,
            ]);
            return response()->json(['ok' => true, 'not_found' => true]);
        }

        $intention->update(['status' => 'refunded']);

        Log::info('Webhook Recurrente: intención marcada como reembolsada', [
            'payment_id' => $paymentId,
            'intention_id' => $intention->id,
        ]);

        return response()->json(['ok' => true, 'refunded' => true]);
    }
}
