# Recurrente + Laravel 12 — Webhook de Donaciones ("Intenciones" normales)
**Estado actual: Fase 4 completada** ✅  
**Última actualización:** 2025-11-20

**Endpoint URL (PROD/TEST según ambiente):**  
`https://basilicadelrosario.gt/api/webhook/recurrente`

**Eventos suscritos en Svix (recomendado y ya configurado):**
- ✅ `payment_intent.succeeded` (**obligatorio**) → crea la intención
- ☑️ `payment_intent.failed` (opcional) → solo log/monitor
- ☑️ `refund.create` (opcional) → marcar como `refunded` si ocurre
- ⛔ `bank_transfer_intent.*` → no usar salvo que se habiliten transferencias bancarias

---

## 📋 Progreso de Implementación

### ✅ Fase 1: Base (COMPLETADA)
- [x] Paquete Svix instalado (v1.81.0)
- [x] Base de datos actualizada con campos Recurrente
  - `payment_intent_id` (varchar 255, nullable, unique)
  - `amount_in_cents` (int unsigned, nullable)
  - `currency` (varchar 3, default 'GTQ')
  - `metadata` (JSON, nullable)
- [x] Modelo `Intention` actualizado con fillables y casts
- [x] Variables de entorno configuradas (.env y .env.example)
- [x] Migración ejecutada exitosamente (migrate:fresh)
- [x] Índices creados para búsqueda rápida

**Estado de BD actual:**
- Tabla: `intentions` con 26 columnas
- Índices: 9 (incluyendo payment_intent_id)
- Collation: utf8mb4_unicode_ci
- Engine: InnoDB

### ✅ Fase 2: Webhook Controller (COMPLETADA)
- [x] Crear `RecurrenteWebhookController`
- [x] Registrar ruta `/api/webhook/recurrente`
- [x] Implementar verificación Svix
- [x] Lógica de idempotencia
- [x] Manejo de evento `payment_intent.succeeded`
- [x] Manejo de eventos adicionales (failed, refund)
- [x] Logging completo de eventos
- [x] Configuración en config/services.php

### ✅ Fase 3: Cliente Recurrente (COMPLETADA)
- [x] Crear `RecurrenteClient` service
- [x] Método `createPaymentIntent()`
- [x] Método `getAmountForIntentionType()` para cálculo de montos
- [x] Método `formatMetadata()` para estructurar datos
- [x] Configuración en config/services.php
- [x] Logging completo de operaciones
- [x] Manejo de errores robusto

### ✅ Fase 4: Frontend Público (COMPLETADA)
- [x] Controlador público para checkout
- [x] Vista de formulario de intenciones (`intentions/form.blade.php`)
- [x] Vista de confirmación exitosa (`intentions/success.blade.php`)
- [x] Vista de cancelación (`intentions/cancel.blade.php`)
- [x] Integración con layout público existente
- [x] Diseño responsive y accesible
- [x] Validación de formularios en frontend y backend
- [x] Botón de descarga de certificado PDF en página de éxito
- [x] Controlador de descarga de certificados (`IntentionCertificateController`)
- [x] Ruta pública para descarga de certificado (`/intenciones/certificado`)

### 🔄 Fase 5: Pruebas (PENDIENTE)
- [ ] Pago TEST con tarjeta de prueba
- [ ] Verificar webhook recibido
- [ ] Validar idempotencia
- [ ] Probar en producción

---

## Objetivo
Registrar **intenciones normales** (no especiales) **solo cuando el pago sea exitoso** en Recurrente. El usuario elige **fecha de misa** y datos básicos; al `payment_intent.succeeded` se crea el registro definitivo en BD.

---

## Convención: **Reusar si existe, crear si falta**
Antes de generar archivos nuevos, Copilot debe **buscar y reutilizar**:
1. Modelo existente: `Intention`, `Donation`, `MassIntention`, `Intencion` (usar el real del proyecto).
2. Tabla existente correspondiente (no crear migración si ya hay).
3. Controlador de webhooks existente (agregar método). Si no lo hay, crear `App\Http\Controllers\Webhook\RecurrenteWebhookController`.
4. Ruta: si existe, reutilizar; si no, crear `POST /api/webhook/recurrente` en `routes/api.php` (sin CSRF).
5. Cliente HTTP a Recurrente: si ya hay, reutilizar; si no, crear uno simple.

---

## Variables de entorno

### ✅ Configuración actual (TEST)
```env
# Svix (firmas de webhook) - ✅ CONFIGURADO
SVIX_WEBHOOK_SECRET=whsec_sz+VI1uoaTR61dMODTVzRjkzTd62+EMN

# Recurrente (TEST) - ✅ CONFIGURADO
RECURRENTE_PUBLIC_KEY=pk_test_CpGa8YL0Dti1z8KHnLiqxuZd27yEXfXGs243O4yz0doTpVSX9ZcwSnLTx
RECURRENTE_SECRET_KEY=sk_test_xxx   # ⚠️ Falta configurar en .env

# URL de la aplicación
APP_URL=https://basilicadelrosario.gt
```

### 📝 Para producción (LIVE - pendiente)
> Cuando estés listo para producción:
1. Cambiar llaves TEST por LIVE en `.env`
2. Crear nuevo endpoint LIVE en Svix
3. Actualizar `SVIX_WEBHOOK_SECRET` con el del endpoint LIVE
4. Ejecutar `php artisan config:clear` y `php artisan optimize`

---

## Contrato de datos (metadata esperada en el Payment Intent)

### Estructura de metadata
Al crear el Payment Intent desde backend, enviar en `metadata`:
```json
{
  "type": "intention",
  "mass_id": 123,
  "intention_type": "rezada",
  "public_text": "Por la salud de Juan Pérez",
  "donor_name": "María González",
  "donor_email": "maria@example.com",
  "donor_phone": "+50212345678"
}
```

### Mapeo con el modelo Intention existente
| Campo Recurrente | Columna BD | Descripción |
|------------------|------------|-------------|
| `payment_intent.id` | `payment_intent_id` | ID único para idempotencia |
| `payment_intent.amount_in_cents` | `amount_in_cents` | Monto exacto en centavos |
| `payment_intent.currency` | `currency` | Moneda (GTQ) |
| `metadata.mass_id` | `mass_instance_id` | FK a la misa seleccionada |
| `metadata.intention_type` | `type` | rezada/cantada/rosario |
| `metadata.public_text` | `public_text` | Texto de la intención |
| `metadata.donor_name` | `donor_name` | Nombre del donante |
| `metadata.donor_email` | `email` | Email del donante |
| `metadata.donor_phone` | `phone` | Teléfono del donante |
| (webhook completo) | `metadata` | JSON completo del evento |

### Montos según tipo
- Rosario: Q30 = 3000 centavos
- Misa rezada: Q50 = 5000 centavos
- Misa cantada: Q150 = 15000 centavos

> **Nota:** Si la API no admite `metadata`, enviar este JSON **firmado** en `description` y el webhook lo parsea.

---

## Ruta del webhook (si no existe)
`routes/api.php`
```php
use App\Http\Controllers\Webhook\RecurrenteWebhookController;

Route::post('/webhook/recurrente', [RecurrenteWebhookController::class, 'handle'])
    ->name('webhook.recurrente');
```

---

## Controlador del webhook (firma Svix + idempotencia + creación)
Instalar verificador de firma:
```bash
composer require svix/svix
```

`app/Http/Controllers/Webhook/RecurrenteWebhookController.php` (adaptar nombres/columnas a lo ya existente):
```php
namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Svix\Webhook;
use Svix\Exception\WebhookVerificationException;

// Reusar modelos reales del proyecto:
use App\Models\Intention;     // o Donation/MassIntention/etc.

class RecurrenteWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret  = env('SVIX_WEBHOOK_SECRET');
        $raw     = $request->getContent();
        $headers = [
            'svix-id'        => $request->header('svix-id'),
            'svix-timestamp' => $request->header('svix-timestamp'),
            'svix-signature' => $request->header('svix-signature'),
        ];

        // 1) Verificar firma Svix
        try { (new Webhook($secret))->verify($raw, $headers); }
        catch (WebhookVerificationException $e) { return response('invalid signature', 400); }

        $payload   = json_decode($raw, true) ?: [];
        $eventType = $payload['event_type'] ?? null;

        // 2) Procesar solo pago exitoso
        if ($eventType === 'payment_intent.succeeded') {
            $paymentId = $payload['id'] ?? null;
            $amountCts = (int)($payload['amount_in_cents'] ?? 0);
            $currency  = $payload['currency'] ?? 'GTQ';
            $paidAt    = isset($payload['created_at']) ? Carbon::parse($payload['created_at']) : now();
            if (!$paymentId || $amountCts <= 0) return response()->json(['ignored' => true]);

            // 3) Idempotencia por payment_intent_id
            if (Intention::where('payment_intent_id', $paymentId)->exists()) {
                return response()->json(['ok' => true]);
            }

            // 4) Crear la intención
            $m = $payload['metadata'] ?? [];
            DB::transaction(function () use ($paymentId, $amountCts, $currency, $paidAt, $m) {
                Intention::create([
                    'type'              => $m['type']        ?? 'normal',
                    'for_date'          => $m['for_date']    ?? null,
                    'for_person'        => $m['for_person']  ?? null,
                    'donor_name'        => $m['donor_name']  ?? null,
                    'donor_email'       => $m['donor_email'] ?? null,
                    'donor_phone'       => $m['donor_phone'] ?? null,
                    'payment_intent_id' => $paymentId,
                    'amount_in_cents'   => $amountCts,
                    'currency'          => $currency,
                    'status'            => 'paid',
                    'paid_at'           => $paidAt,
                    'metadata'          => $m ?: null,
                ]);
            });
        }

        // (Opcional) Log de fallidos/refunds si están suscritos
        return response()->json(['ok' => true]);
    }
}
```

> **Importante:** No duplicar migraciones. Ajustar el `create()` a los nombres de columnas **ya existentes** en tu tabla.

---

## Servicio para crear Payment Intent (reusar/crear)
Pseudocódigo con el **HTTP client** de Laravel; si ya hay servicio, reutilizar y solo enviar `metadata`.

```php
use Illuminate\Support\Facades\Http;

class RecurrenteClient
{
    public function __construct(private string $secret) {}

    public function createPaymentIntent(int $amountCents, string $currency, array $metadata): string
    {
        $resp = Http::withToken($this->secret)
            ->post('https://api.recurrente.com/v1/payment_intents', [
                'amount_in_cents' => $amountCents,
                'currency'        => $currency, // 'GTQ'
                'metadata'        => $metadata,
            ])
            ->throw()
            ->json();

        return $resp['checkout_url'] ?? throw new \RuntimeException('No checkout_url');
    }
}
```

Controlador público (ejemplo):
```php
public function pagarIntencion(NormalIntentionRequest $req, RecurrenteClient $client)
{
    $data        = $req->validated();
    $amountCents = config('parroquia.amount_intention_cents', 5000); // Q50 (ajustar)

    // (opcional) Validar cupo por fecha aquí

    $checkoutUrl = $client->createPaymentIntent($amountCents, 'GTQ', [
        'type'        => 'normal',
        'for_date'    => $data['for_date'],
        'for_person'  => $data['for_person'],
        'donor_name'  => $data['donor_name'],
        'donor_email' => $data['donor_email'],
        'donor_phone' => $data['donor_phone'],
    ]);

    return redirect()->away($checkoutUrl);
}
```

---

## Pruebas rápidas
1. En Svix (endpoint creado) → **Send Test** con `payment_intent.succeeded`. Debe responder **200**.
2. Confirmar inserción en BD (sin duplicados por `payment_intent_id`).
3. Hacer pago real en **TEST** (monto bajo) y verificar:
   - Llega webhook (Svix Logs: Delivered 2XX).
   - Se crea la intención con `for_date` y datos de metadata.

---

## Paso a LIVE
1. Completar KYC y límites con Recurrente si aplica.  
2. Cambiar llaves a **LIVE** (`pk_live_…`, `sk_live_…`).  
3. Crear **endpoint LIVE** en Svix con **la misma URL** y **nuevo** `SVIX_WEBHOOK_SECRET`.  
4. `php artisan optimize:clear` y prueba un pago real pequeño.

---

## Checklist de Implementación

### ✅ Fase 1 - Base (Completada)
- [x] Instalar paquete Svix (`composer require svix/svix`)
- [x] Agregar columnas a tabla `intentions`:
  - [x] `payment_intent_id` (unique, nullable)
  - [x] `amount_in_cents` (int unsigned, nullable)
  - [x] `currency` (varchar 3, default 'GTQ')
  - [x] `metadata` (JSON, nullable)
- [x] Actualizar modelo `Intention` con fillables y casts
- [x] Configurar variables en `.env` y `.env.example`
- [x] Ejecutar migración (`php artisan migrate:fresh --seed`)
- [x] Verificar estructura de BD correcta

### ✅ Fase 2 - Webhook (Completada)
- [x] Crear `app/Http/Controllers/Webhook/RecurrenteWebhookController.php`
- [x] Implementar verificación de firma Svix
- [x] Lógica de idempotencia por `payment_intent_id`
- [x] Mapear metadata a columnas del modelo
- [x] Crear intención solo en `payment_intent.succeeded`
- [x] Registrar ruta en `routes/api.php` (sin CSRF)
- [x] Agregar configuración en `config/services.php`
- [ ] Probar con "Send Test" desde Svix

### ✅ Fase 3 - Cliente Recurrente (Completada)
- [x] Crear `app/Services/RecurrenteClient.php`
- [x] Método `createPaymentIntent()` con HTTP client
- [x] Métodos helper para montos y metadata
- [x] Crear `CreateIntentionCheckoutRequest` para validación
- [x] Crear `PublicIntentionController` para checkout público
- [x] Registrar rutas públicas en `routes/web.php`
- [x] Manejo de errores de API de Recurrente
- [x] Logging de todas las operaciones

### ✅ Fase 4 - Frontend Público (Completada)
- [x] Controlador público `PublicIntentionController@checkout`
- [x] Vista de formulario con selección de tipo de intención
- [x] Formulario de datos del donante con validación
- [x] Página de confirmación post-pago (success)
- [x] Página de cancelación con opciones alternativas
- [x] Diseño integrado con el tema de la parroquia
- [x] Experiencia de usuario optimizada
- [x] Información de contacto y ayuda

### 🔄 Fase 5 - Pruebas (Pendiente)
- [ ] Pago TEST con tarjeta Visa 4242424242424242
- [ ] Verificar webhook recibido en logs de Svix
- [ ] Validar intención creada en BD
- [ ] Probar idempotencia (reenvío de mismo webhook)
- [ ] Validar que no se excede cupo de misa

## Próximos pasos inmediatos
1. ✅ ~~Crear RecurrenteWebhookController con verificación Svix~~
2. ✅ ~~Registrar ruta `/api/webhook/recurrente`~~
3. ✅ ~~Crear `RecurrenteClient` para generar Payment Intents~~
4. ✅ ~~Crear vistas de formulario público~~
5. **Fase 5:** Hacer prueba end-to-end completa con tarjeta de TEST
6. **Probar webhook** desde panel de Svix → https://app.svix.com
7. **Producción:** Cambiar a llaves LIVE y crear endpoint LIVE en Svix
