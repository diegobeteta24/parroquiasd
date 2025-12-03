<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente para interactuar con la API de Recurrente.
 * 
 * Documentación API: https://recurrente.com/docs
 */
class RecurrenteClient
{
    protected string $publicKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->publicKey = config('services.recurrente.public_key') ?? env('RECURRENTE_PUBLIC_KEY');
        $this->secretKey = config('services.recurrente.secret_key') ?? env('RECURRENTE_SECRET_KEY');
        $this->baseUrl = config('services.recurrente.base_url', 'https://app.recurrente.com');

        if (empty($this->publicKey) || empty($this->secretKey)) {
            throw new \RuntimeException('RECURRENTE_PUBLIC_KEY y RECURRENTE_SECRET_KEY deben estar configurados');
        }
    }

    /**
     * Crea un checkout en Recurrente y retorna la respuesta completa.
     * 
     * @param string $productId ID del producto en Recurrente
     * @param array $metadata Metadata adicional para el checkout
     * @param string|null $successUrl URL de redirección en caso de éxito
     * @param string|null $cancelUrl URL de redirección en caso de cancelación
     * @return array Respuesta de la API con checkout_url
     * @throws \RuntimeException
     */
    public function createCheckout(string $productId, array $metadata = [], ?string $successUrl = null, ?string $cancelUrl = null): array
    {
        // Construir payload
        $payload = [
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity'   => 1,
                ],
            ],
        ];

        // Agregar URLs si están definidas
        if ($successUrl) {
            $payload['success_url'] = $successUrl;
        }
        if ($cancelUrl) {
            $payload['cancel_url'] = $cancelUrl;
        }
        // Agregar metadata si está definida
        if (!empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        Log::info('Recurrente: Creando checkout', [
            'product_id' => $productId,
            'metadata' => $metadata,
            'payload' => $payload,
        ]);

        try {
            $response = Http::withHeaders([
                'X-PUBLIC-KEY' => $this->publicKey,
                'X-SECRET-KEY' => $this->secretKey,
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post("{$this->baseUrl}/api/checkouts", $payload);

            // Log the response for debugging
            Log::info('Recurrente: Respuesta HTTP', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500),
                'successful' => $response->successful(),
            ]);

            // Check if response is successful
            if (!$response->successful()) {
                Log::error('Recurrente: Error HTTP en la API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('No se pudo crear el checkout en Recurrente. Por favor intenta más tarde o contáctanos al 2502-2727.');
            }

            // Attempt to parse JSON
            $jsonResponse = $response->json();
            
            if (!$jsonResponse) {
                Log::error('Recurrente: Respuesta no es JSON válido', [
                    'content_type' => $response->header('Content-Type'),
                    'body_preview' => substr($response->body(), 0, 500),
                ]);
                throw new \RuntimeException('El servicio de pagos no está disponible temporalmente. Por favor intenta más tarde.');
            }

            Log::info('Recurrente: Checkout creado exitosamente', [
                'response' => $jsonResponse,
            ]);

            return $jsonResponse;

        } catch (\RuntimeException $e) {
            // Re-throw RuntimeExceptions with our custom messages
            throw $e;

        } catch (\Exception $e) {
            Log::error('Recurrente: Error inesperado', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            throw new \RuntimeException('Hubo un problema al procesar tu pago. Por favor contáctanos al 2502-2727.');
        }
    }

    /**
     * Calcula el monto en centavos según el tipo de intención.
     * 
     * @param string $intentionType Tipo: normal, rosario, etc.
     * @return int Monto en centavos
     */
    public static function getAmountForIntentionType(string $intentionType): int
    {
        return match ($intentionType) {
            'rosario' => 3000,      // Q30.00
            'normal' => 5000,        // Q50.00 (rezada)
            'rezada' => 5000,        // Q50.00
            'cantada' => 15000,      // Q150.00
            default => 5000,         // Default: Q50.00
        };
    }

    /**
     * Obtiene el Product ID de Recurrente según el tipo de intención.
     * 
     * Por ahora todas las intenciones usan el mismo producto de "Acción de Gracias".
     * 
     * @param string $intentionType Tipo: rosario, normal/rezada, cantada
     * @return string Product ID de Recurrente
     * @throws \RuntimeException Si no hay Product ID configurado
     */
    public static function getProductIdForIntentionType(string $intentionType): string
    {
        $normalizedType = match ($intentionType) {
            'rosario' => 'rosario',
            'cantada' => 'cantada',
            default => 'rezada', // incluye "normal" y "rezada"
        };

        $products = config('services.recurrente.products', []);
        $productId = $products[$normalizedType] ?? null;

        if (empty($productId)) {
            $fallbacks = [
                'rezada' => env('RECURRENTE_PRODUCT_INTENCION_ACCION_GRACIAS'),
                'rosario' => env('RECURRENTE_PRODUCT_ROSARIO'),
                'cantada' => env('RECURRENTE_PRODUCT_CANTADA'),
            ];

            $productId = $fallbacks[$normalizedType] ?? null;
        }

        if (empty($productId)) {
            throw new \RuntimeException(sprintf(
                'No hay un Product ID configurado para intenciones de tipo "%s". Revisa las variables RECURRENTE_PRODUCT_*.',
                $normalizedType
            ));
        }

        return $productId;
    }

    /**
     * Formatea metadata para enviar a Recurrente.
     * 
     * @param array $data Datos del formulario
     * @return array Metadata estructurada
     */
    public static function formatMetadata(array $data): array
    {
        return [
            'mass_id' => $data['mass_instance_id'] ?? null,
            'intention_type' => $data['intention_type'] ?? 'normal',
            'category' => $data['category'] ?? null,
            'public_text' => $data['public_text'] ?? null,
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'donor_phone' => $data['donor_phone'] ?? null,
            'dedicatee_name' => $data['dedicatee_name'] ?? null,
        ];
    }
}
