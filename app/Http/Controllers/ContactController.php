<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    /**
     * Muestra el formulario de contacto.
     */
    public function show()
    {
        return view('pages.contacto');
    }

    /**
     * Procesa el envío del formulario de contacto.
     */
    public function send(Request $request)
    {
        // Rate limiting para evitar spam (5 intentos por IP cada 10 minutos)
        $key = 'contact-form:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->with('error', "Por favor espera {$seconds} segundos antes de enviar otro mensaje.");
        }
        
        RateLimiter::hit($key, 600); // 10 minutos

        // Validación de datos
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'asunto' => ['required', 'string', 'in:informacion_general,bautismo,matrimonio,confirmacion,confesion,uncion,intenciones,voluntariado,otro'],
            'mensaje' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'nombre.required' => 'Por favor ingresa tu nombre.',
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'Por favor ingresa un correo electrónico válido.',
            'asunto.required' => 'Por favor selecciona un asunto.',
            'asunto.in' => 'Por favor selecciona un asunto válido.',
            'mensaje.required' => 'Por favor escribe tu mensaje.',
            'mensaje.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'mensaje.max' => 'El mensaje no puede exceder 2000 caracteres.',
        ]);

        // Mapeo de asuntos para mostrar nombres amigables
        $asuntos = [
            'informacion_general' => 'Información general',
            'bautismo' => 'Bautismo',
            'matrimonio' => 'Matrimonio',
            'confirmacion' => 'Confirmación / Primera Comunión',
            'confesion' => 'Confesión / Dirección espiritual',
            'uncion' => 'Unción de enfermos',
            'intenciones' => 'Intenciones de misa',
            'voluntariado' => 'Voluntariado',
            'otro' => 'Otro',
        ];

        $validated['asunto_display'] = $asuntos[$validated['asunto']] ?? $validated['asunto'];

        try {
            // Enviar correo al despacho parroquial
            $parishEmail = config('portal.parish.contact.email');
            
            if ($parishEmail) {
                Mail::send('emails.contacto', $validated, function ($mail) use ($validated, $parishEmail) {
                    $mail->to($parishEmail)
                         ->replyTo($validated['email'], $validated['nombre'])
                         ->subject('Contacto Web: ' . $validated['asunto_display']);
                });
            }

            // Registrar en log para seguimiento
            Log::info('Formulario de contacto enviado', [
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'asunto' => $validated['asunto'],
                'ip' => $request->ip(),
            ]);

            return back()->with('success', '¡Gracias por contactarnos! Responderemos a tu mensaje lo antes posible. Que Dios te bendiga.');

        } catch (\Exception $e) {
            Log::error('Error al enviar formulario de contacto', [
                'error' => $e->getMessage(),
                'email' => $validated['email'],
            ]);

            return back()
                ->withInput()
                ->with('error', 'Hubo un problema al enviar tu mensaje. Por favor intenta de nuevo o contáctanos por WhatsApp.');
        }
    }
}
