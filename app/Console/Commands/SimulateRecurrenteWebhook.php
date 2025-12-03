<?php

namespace App\Console\Commands;

use App\Models\Intention;
use App\Models\MassInstance;
use App\Services\RecurrenteClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Svix\Webhook;

class SimulateRecurrenteWebhook extends Command
{
    protected $signature = 'webhook:simulate-recurrente {type=normal : Tipo de intención (normal, rosario, cantada)} {--mass_id=}';

    protected $description = 'Simula un webhook payment_intent.succeeded de Recurrente/Svix dentro del entorno local';

    public function handle(): int
    {
        $type = strtolower((string) $this->argument('type'));

        $type = match ($type) {
            'rosario' => 'rosario',
            'cantada' => 'cantada',
            'normal', 'rezada' => 'normal',
            default => null,
        };

        if ($type === null) {
            $this->error('Tipo de intención inválido. Usa: normal, rosario o cantada.');
            return self::FAILURE;
        }

        $secret = config('services.svix.webhook_secret') ?? env('SVIX_WEBHOOK_SECRET');
        if (empty($secret)) {
            $this->error('SVIX_WEBHOOK_SECRET no está configurado.');
            return self::FAILURE;
        }

        $amountCents = RecurrenteClient::getAmountForIntentionType($type === 'normal' ? 'normal' : $type);

        $mass = $this->resolveMassInstance($type);
        if (!$mass) {
            $this->error('No fue posible encontrar o crear una misa para la simulación.');
            return self::FAILURE;
        }

        $metadata = $this->buildMetadata($type, $mass->id);
        $payload = $this->buildPayload($type, $amountCents, $metadata);
        $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $webhookId = 'msg_' . Str::random(16);
        $timestamp = (string) time();
        $signature = (new Webhook($secret))->sign($webhookId, $timestamp, $rawPayload);

        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_SVIX-ID' => $webhookId,
            'HTTP_SVIX-TIMESTAMP' => $timestamp,
            'HTTP_SVIX-SIGNATURE' => $signature,
            'REMOTE_ADDR' => '127.0.0.1',
        ];

        $request = Request::create(
            '/api/webhook/recurrente',
            'POST',
            server: $server,
            content: $rawPayload
        );

        $response = app()->handle($request);

        $this->info(sprintf('Respuesta HTTP %s', $response->getStatusCode()));
        $this->line($response->getContent());

        $intention = Intention::where('payment_intent_id', $payload['id'])->first();

        if ($intention) {
            $this->table([
                'ID', 'Tipo', 'Mass ID', 'Monto (GTQ)', 'Product Type'
            ], [[
                $intention->id,
                $intention->type,
                $intention->mass_instance_id,
                $intention->amount,
                $type,
            ]]);
        } else {
            $this->warn('No se encontró la intención creada. Revisa los logs para más detalles.');
        }

        return self::SUCCESS;
    }

    protected function resolveMassInstance(string $type): ?MassInstance
    {
        $query = MassInstance::query()->where('starts_at', '>', now());

        if ($type === 'rosario') {
            $query->where('is_special', true)->where('special_category', 'rosario');
        } else {
            $query->where(function ($q) {
                $q->where('is_special', false)->orWhereNull('is_special');
            });
        }

        $mass = $query->orderBy('starts_at')->first();

        if ($mass) {
            return $mass;
        }

        return MassInstance::factory()->create([
            'starts_at' => Carbon::now()->addDays(2),
            'is_special' => $type === 'rosario',
            'special_category' => $type === 'rosario' ? 'rosario' : null,
            'capacity' => 50,
            'occupied' => 0,
            'status' => 'scheduled',
        ]);
    }

    protected function buildMetadata(string $type, int $massId): array
    {
        $category = match ($type) {
            'rosario' => 'peticiones',
            'cantada' => 'acciones_de_gracia',
            default => 'difuntos',
        };

        return [
            'mass_id' => (string) $massId,
            'intention_type' => $type,
            'category' => $category,
            'public_text' => ucfirst($type) . ' de prueba local',
            'donor_name' => 'Simulación ' . ucfirst($type),
            'donor_email' => sprintf('simu+%s@example.com', $type),
            'donor_phone' => null,
            'dedicatee_name' => 'Test ' . ucfirst($type),
        ];
    }

    protected function buildPayload(string $type, int $amountCents, array $metadata): array
    {
        $paymentId = 'pi_local_' . $type . '_' . Str::random(8);
        $checkoutId = 'ch_local_' . Str::random(10);

        return [
            'id' => $paymentId,
            'event_type' => 'payment_intent.succeeded',
            'amount_in_cents' => $amountCents,
            'currency' => 'GTQ',
            'created_at' => Carbon::now()->toIso8601String(),
            'metadata' => $metadata,
            'checkout' => [
                'id' => $checkoutId,
                'status' => 'paid',
                'currency' => 'GTQ',
                'metadata' => $metadata,
                'payment_method' => [
                    'id' => 'pay_local_' . Str::random(8),
                    'type' => 'card',
                    'card' => [
                        'last4' => '4242',
                        'network' => 'visa',
                    ],
                ],
                'payment' => [
                    'id' => 'pa_local_' . Str::random(8),
                    'paymentable' => [
                        'id' => 'on_local_' . Str::random(6),
                        'type' => 'OneTimePayment',
                    ],
                ],
                'total_in_cents' => $amountCents,
                'success_url' => config('app.url') . '/intenciones/success',
                'cancel_url' => config('app.url') . '/intenciones/cancel',
            ],
            'customer' => [
                'id' => 'us_local_' . Str::random(6),
                'full_name' => $metadata['donor_name'],
                'email' => $metadata['donor_email'],
            ],
            'products' => [],
        ];
    }
}
