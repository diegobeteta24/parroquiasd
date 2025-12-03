<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIntentionCheckoutRequest;
use App\Models\Intention;
use App\Models\MassInstance;
use App\Services\RecurrenteClient;
use Illuminate\Support\Facades\Log;

/**
 * Controlador público para gestionar el checkout de intenciones vía Recurrente.
 */
class PublicIntentionController extends Controller
{
    protected RecurrenteClient $recurrenteClient;

    public function __construct(RecurrenteClient $recurrenteClient)
    {
        $this->recurrenteClient = $recurrenteClient;
    }

    /**
     * Muestra el formulario público con la lista de misas disponibles.
     */
    public function form()
    {
        if (!config('portal.intentions_enabled', true)) {
            return view('intentions.disabled');
        }

        $now = now()->timezone(config('app.timezone'))->startOfDay();
        $nextDay = $now->copy()->addDay();

        $regularMasses = MassInstance::query()
            ->withCount('intentions')
            ->where('status', 'scheduled')
            ->where('is_special', false)
            ->where('starts_at', '>=', $nextDay)
            ->orderBy('starts_at')
            ->limit(50)
            ->get()
            ->filter(function (MassInstance $mass) {
                if ($mass->capacity === null) {
                    return true;
                }

                return $mass->intentions_count < $mass->capacity;
            })
            ->values();

        $rosaryMasses = MassInstance::query()
            ->withCount('intentions')
            ->where('status', 'scheduled')
            ->where('is_special', true)
            ->where('special_category', 'rosario')
            ->where('starts_at', '>=', $nextDay)
            ->orderBy('starts_at')
            ->limit(30)
            ->get();

        return view('intentions.form', [
            'regularMasses' => $regularMasses,
            'rosaryMasses' => $rosaryMasses,
        ]);
    }

    /**
     * Crea un checkout en Recurrente y redirige al usuario.
     * 
     * @param CreateIntentionCheckoutRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(CreateIntentionCheckoutRequest $request)
    {
        if (!config('portal.intentions_enabled', true)) {
            abort(503, 'Las intenciones en línea están temporalmente suspendidas.');
        }

        $validated = $request->validated();

        try {
            // Obtener el Product ID según el tipo de intención
            $intentionType = $validated['intention_type'];
            $productId = RecurrenteClient::getProductIdForIntentionType($intentionType);

            // Formatear metadata
            $metadata = RecurrenteClient::formatMetadata($validated);

            // Crear checkout en Recurrente
            $result = $this->recurrenteClient->createCheckout(
                $productId,
                $metadata,
                route('intentions.success'),
                route('intentions.cancel')
            );

            // Extraer URL del checkout (puede ser 'url', 'checkout_url' o similar)
            $checkoutUrl = $result['checkout_url'] ?? $result['url'] ?? null;

            if (!$checkoutUrl) {
                Log::error('Recurrente: No se encontró URL de checkout en la respuesta', [
                    'response' => $result,
                ]);
                throw new \RuntimeException('No se pudo obtener la URL de pago.');
            }

            Log::info('Checkout de intención iniciado', [
                'product_id' => $productId,
                'intention_type' => $intentionType,
                'donor_email' => $validated['donor_email'],
                'checkout_id' => $result['id'] ?? null,
                'checkout_url' => $checkoutUrl,
            ]);

            if (isset($result['id'])) {
                session()->put('intentions.checkout', [
                    'checkout_id' => $result['id'],
                    'donor_email' => $validated['donor_email'],
                    'created_at' => now()->toIso8601String(),
                ]);
            }

            // Redirigir al checkout de Recurrente
            return redirect()->away($checkoutUrl);

        } catch (\RuntimeException $e) {
            Log::error('Error al crear checkout', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Error de conexión con Recurrente', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'No pudimos conectar con el sistema de pagos. Por favor verifica tu conexión a internet e intenta de nuevo, o contáctanos por WhatsApp al 5628-0420.']);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Error al crear checkout', [
                'error' => $e->getMessage(),
                'status' => $e->response?->status(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'No pudimos procesar tu solicitud de pago. Por favor intenta de nuevo o contáctanos al 2502-2727.']);

        } catch (\Exception $e) {
            Log::error('Error inesperado en checkout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error inesperado. Por favor intenta de nuevo más tarde o contáctanos al 2502-2727 para asistencia.']);
        }
    }

    /**
     * Página de confirmación después del pago (success callback).
     * 
     * @return \Illuminate\View\View
     */
    public function success()
    {
        $checkoutContext = session('intentions.checkout');
        $intention = null;

        if ($checkoutContext && ($checkoutId = $checkoutContext['checkout_id'] ?? null)) {
            $intention = Intention::query()
                ->with('dedicatee')
                ->where('metadata->checkout->id', $checkoutId)
                ->latest('id')
                ->first();
        }

        $personalCertificateUrl = $intention?->certificate_url;

        return view('intentions.success', [
            'title' => 'Pago Exitoso',
            'message' => 'Tu intención ha sido registrada exitosamente. Recibirás un correo de confirmación.',
            'intention' => $intention,
            'personalCertificateUrl' => $personalCertificateUrl,
            'genericCertificateUrl' => route('intentions.certificate.download'),
            'awaitingCertificate' => $checkoutContext && !$intention,
        ]);
    }

    /**
     * Página de cancelación (cuando el usuario cancela el pago).
     * 
     * @return \Illuminate\View\View
     */
    public function cancel()
    {
        return view('intentions.cancel', [
            'title' => 'Pago Cancelado',
            'message' => 'Has cancelado el proceso de pago. Puedes intentarlo de nuevo cuando quieras.',
        ]);
    }
}
