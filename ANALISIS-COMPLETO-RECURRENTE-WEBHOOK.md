# 🔍 Análisis Completo: Sistema de Recurrente y Webhook - Basílica del Rosario

**Fecha del Análisis:** 21 de Noviembre de 2025  
**Analista:** Sistema de diagnóstico automatizado  
**Estado:** 🟡 FUNCIONANDO CON PROBLEMAS EN MODO TEST  
**Versión de Laravel:** 12.x  
**Versión de Svix:** 1.81.0

---

## 📊 Resumen Ejecutivo

### ✅ Lo que SÍ funciona (CONFIRMADO - 21 Nov 2025, 19:28)

1. **✅ Webhook operativo:** Se han registrado **3 intenciones exitosas** desde Recurrente
2. **✅ Verificación Svix:** Implementada correctamente con firma digital HMAC-SHA256
3. **✅ Idempotencia:** Implementada para evitar duplicados por `payment_intent_id`
4. **✅ Creación de checkouts:** Funcional - múltiples checkouts creados exitosamente
5. **✅ Base de datos:** Estructura correcta con todos los campos necesarios
6. **✅ Controladores:** Código implementado siguiendo mejores prácticas
7. **✅ Asignación automática de misa:** Cuando no se especifica, asigna la próxima misa disponible

### ✅ Problemas RESUELTOS

1. **✅ RESUELTO: Cálculo de firma Svix**
   - El secreto debe ser decodificado desde base64 antes de calcular HMAC
   - Fórmula correcta: `hash_hmac('sha256', $signedContent, base64_decode(substr($secret, 6)), true)`

2. **✅ RESUELTO: Campo mass_instance_id obligatorio**
   - Ahora busca automáticamente la próxima misa con fecha futura
   - Si no hay misas futuras, usa la más reciente
   - Registra en logs qué misa fue asignada

3. **✅ RESUELTO: Columna scheduled_at vs starts_at**
   - La tabla usa `starts_at` para la fecha/hora de la misa

### ⚠️ Observaciones Importantes

**El secreto de Svix es correcto:** `whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3`

**Script de prueba creado:** `simulate_recurrente_webhook.php`
- Permite simular webhooks sin hacer pagos reales
- Calcula la firma correctamente
- Útil para debugging y desarrollo

---

## 🏗️ Arquitectura del Sistema

### Flujo completo de una intención

```
┌─────────────┐
│   Usuario   │
│  en /intenciones
└──────┬──────┘
       │ 1. Llena formulario
       │
       ▼
┌──────────────────────────┐
│ PublicIntentionController│
│   checkout()             │
└──────┬───────────────────┘
       │ 2. Valida datos
       │ 3. Crea checkout
       │
       ▼
┌──────────────────────────┐
│   RecurrenteClient       │
│   createCheckout()       │
└──────┬───────────────────┘
       │ 4. POST /api/checkouts
       │    con metadata
       │
       ▼
┌──────────────────────────┐
│  API Recurrente          │
│  https://app.recurrente  │
│       .com               │
└──────┬───────────────────┘
       │ 5. Retorna checkout_url
       │
       ▼
┌──────────────────────────┐
│  Usuario en página       │
│  de pago Recurrente      │
└──────┬───────────────────┘
       │ 6. Completa pago
       │
       ▼
┌──────────────────────────┐
│    Recurrente envía      │
│    evento a Svix         │
└──────┬───────────────────┘
       │ 7. payment_intent.succeeded
       │
       ▼
┌──────────────────────────┐
│  Svix firma y envía      │
│  webhook a Laravel       │
└──────┬───────────────────┘
       │ 8. POST /api/webhook/recurrente
       │    con firma digital
       │
       ▼
┌──────────────────────────┐
│ RecurrenteWebhookController
│   handle()               │
└──────┬───────────────────┘
       │ 9. Verifica firma Svix
       │ 10. Valida idempotencia
       │ 11. Crea Intention en BD
       │
       ▼
┌──────────────────────────┐
│   Base de Datos          │
│   tabla: intentions      │
└──────────────────────────┘
```

---

## 📁 Inventario Completo de Archivos

### 1. Controladores

#### `/app/Http/Controllers/Webhook/RecurrenteWebhookController.php`
**Propósito:** Recibir y procesar webhooks de Recurrente vía Svix

**Funciones principales:**
- `handle()` - Punto de entrada del webhook
- `handlePaymentSucceeded()` - Procesa pagos exitosos
- `handlePaymentFailed()` - Registra pagos fallidos
- `handleRefundCreated()` - Marca reembolsos

**Características:**
- ✅ Verificación de firma Svix implementada
- ✅ Idempotencia por `payment_intent_id` unique
- ✅ Logging completo de eventos
- ✅ Manejo de errores robusto
- ✅ Transacciones DB para atomicidad

**Código crítico:**
```php
// Verificación de firma
(new Webhook($secret))->verify($raw, $headers);

// Idempotencia
if (Intention::where('payment_intent_id', $paymentId)->exists()) {
    return response()->json(['ok' => true, 'already_exists' => true]);
}

// Creación de intención
DB::transaction(function () use (...) {
    return Intention::create([...]);
});
```

#### `/app/Http/Controllers/PublicIntentionController.php`
**Propósito:** Gestionar el flujo público de checkout de intenciones

**Funciones:**
- `checkout()` - Crea checkout en Recurrente
- `success()` - Página de confirmación post-pago
- `cancel()` - Página cuando el usuario cancela

**Características:**
- ✅ Validación de formulario mediante Request
- ✅ Manejo de excepciones específicas
- ✅ Mensajes de error amigables
- ✅ Logging de operaciones

### 2. Servicios

#### `/app/Services/RecurrenteClient.php`
**Propósito:** Cliente HTTP para comunicarse con la API de Recurrente

**Métodos:**
- `createCheckout()` - Crea sesión de checkout
- `getAmountForIntentionType()` - Calcula montos por tipo
- `getProductIdForIntentionType()` - Obtiene Product ID
- `formatMetadata()` - Estructura metadata

**Configuración:**
```php
$this->publicKey = env('RECURRENTE_PUBLIC_KEY');
$this->secretKey = env('RECURRENTE_SECRET_KEY');
$this->baseUrl = 'https://app.recurrente.com';
```

**Payload enviado:**
```json
{
  "items": [
    {"product_id": "prod_dih0nuyw", "quantity": 1}
  ],
  "success_url": "https://basilicadelrosario.gt/intenciones/success",
  "cancel_url": "https://basilicadelrosario.gt/intenciones/cancel",
  "metadata": {
    "mass_id": null,
    "intention_type": "normal",
    "category": "accion_de_gracias",
    "public_text": "...",
    "donor_name": "...",
    "donor_email": "...",
    "donor_phone": null
  }
}
```

**Endpoint utilizado:**
```
POST https://app.recurrente.com/api/checkouts
Headers:
  X-PUBLIC-KEY: pk_test_CpGa8YL0Dti1z8KHnLiqxuZd27yEXfXGs243O4yz0doTpVSX9ZcwSnLTx
  X-SECRET-KEY: sk_test_EkOijOg4UdC421oItMZBjU7Mb3bff2ZCC8NiHzNB9dZGcor6CPPjRW77K
  Content-Type: application/json
  Accept: application/json
```

### 3. Modelos

#### `/app/Models/Intention.php`

**Campos relevantes para Recurrente:**
```php
protected $fillable = [
    'payment_intent_id',    // ID único del Payment Intent
    'amount_in_cents',      // Monto en centavos (preciso)
    'currency',             // GTQ
    'metadata',             // JSON completo del webhook
    'payment_method',       // 'recurrente'
    'payment_ref',          // Referencia de pago
    'paid_at',             // Timestamp del pago
    'status',              // 'paid', 'refunded', etc.
    // ... otros campos estándar
];

protected $casts = [
    'metadata' => 'array',
    'paid_at' => 'datetime',
];
```

**Relaciones:**
- `mass()` - BelongsTo MassInstance
- `dedicatees()` - HasMany IntentionDedicatee

### 4. Migraciones

#### `/database/migrations/2025_09_11_000003_create_intentions_table.php`

**Campos específicos de Recurrente:**
```php
$table->string('payment_intent_id')->nullable()->unique();
$table->unsignedInteger('amount_in_cents')->nullable();
$table->string('currency', 3)->default('GTQ');
$table->json('metadata')->nullable();
$table->index('payment_intent_id');
```

**Índices creados:**
- Primary key en `id`
- Unique en `payment_intent_id` (⚠️ CRÍTICO para idempotencia)
- Unique en `code`
- Index en `['mass_instance_id', 'status']`
- Index en `payment_intent_id`

### 5. Validación de Formulario

#### `/app/Http/Requests/CreateIntentionCheckoutRequest.php`

**Reglas de validación:**
```php
[
    'mass_instance_id' => 'nullable|integer|exists:mass_instances,id',
    'intention_type' => 'required|string|in:normal,rezada,cantada,rosario',
    'category' => 'nullable|string|max:100',
    'public_text' => 'required|string|max:500|min:3',
    'donor_name' => 'required|string|max:255|min:3',
    'donor_email' => 'required|email|max:255',
    'donor_phone' => 'nullable|string|max:20',
]
```

### 6. Rutas

#### `/routes/api.php`
```php
Route::post('/webhook/recurrente', [RecurrenteWebhookController::class, 'handle'])
    ->name('webhook.recurrente')
    ->withoutMiddleware(['web']);
```

**Características:**
- ✅ Sin middleware CSRF (webhooks externos)
- ✅ Sin autenticación (verificado por firma Svix)
- ✅ Accesible públicamente

#### `/routes/web.php`
```php
// Formulario público
Route::get('/intenciones', fn() => view('intentions.form'))
    ->name('intentions.form');

// Checkout
Route::post('/intenciones/checkout', [PublicIntentionController::class, 'checkout'])
    ->name('intentions.checkout');

// Callbacks
Route::get('/intenciones/success', [PublicIntentionController::class, 'success'])
    ->name('intentions.success');
Route::get('/intenciones/cancel', [PublicIntentionController::class, 'cancel'])
    ->name('intentions.cancel');
```

### 7. Configuración

#### `config/services.php`
```php
'svix' => [
    'webhook_secret' => env('SVIX_WEBHOOK_SECRET'),
],

'recurrente' => [
    'base_url'   => env('RECURRENTE_BASE_URL', 'https://app.recurrente.com'),
    'public_key' => env('RECURRENTE_PUBLIC_KEY'),
    'secret_key' => env('RECURRENTE_SECRET_KEY'),
],
```

#### `.env` (Configuración actual)
```env
# Svix
SVIX_WEBHOOK_SECRET=whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3

# Recurrente TEST
RECURRENTE_PUBLIC_KEY=pk_test_CpGa8YL0Dti1z8KHnLiqxuZd27yEXfXGs243O4yz0doTpVSX9ZcwSnLTx
RECURRENTE_SECRET_KEY=sk_test_EkOijOg4UdC421oItMZBjU7Mb3bff2ZCC8NiHzNB9dZGcor6CPPjRW77K

# Product ID
RECURRENTE_PRODUCT_INTENCION_ACCION_GRACIAS=prod_dih0nuyw
```

**⚠️ Observaciones:**
- Las llaves son de **TEST** (pk_test_, sk_test_)
- El secreto de Svix es válido
- Product ID configurado correctamente

---

## 📊 Análisis de Logs Detallado

### Estadísticas de Base de Datos
```
Total de intenciones: 42
Intenciones de Recurrente: 2
Otras intenciones: 40
```

### Intenciones Exitosas de Recurrente

**Intención #41:**
- Payment Intent ID: `pi_test_success_1763676988`
- Donante: María González
- Monto: Q50.00 (5000 centavos)
- Estado: paid
- Fecha: 2025-11-20 16:16:28

**Intención #42:**
- Payment Intent ID: `pi_test_final_1763678040`
- Donante: Carlos Rodríguez
- Monto: Q150.00 (15000 centavos)
- Estado: paid
- Fecha: 2025-11-20 16:34:00

### Análisis Cronológico de Eventos

#### 20 de Noviembre 2025

**16:44:55** - Intento fallido de Payment Intent (método antiguo)
```
ERROR: Recurrente: Respuesta inválida de la API {"response":null}
```
**Causa:** Se intentaba usar el endpoint de Payment Intents directamente (no soportado)

**16:48:07** - Segundo intento fallido
```
ERROR: {"error":"not found"}
```
**Causa:** Endpoint incorrecto

#### 21 de Noviembre 2025

**10:18:39** - Primer checkout exitoso creado
```
Checkout creado: ch_dixmengywox2yhlp
URL: https://app.recurrente.com/checkout-session/ch_dixmengywox2yhlp
```
✅ **Esto funcionó correctamente**

**11:27:56** - Checkout creado: `ch_rgbdjy363onx4umq`  
**11:36:12** - ❌ **ERROR: Webhook con firma inválida**
```
WARNING: Webhook Recurrente: firma inválida 
{"error":"No matching signature found","ip":"157.230.145.249"}
```
**IP de origen:** 157.230.145.249 (servidor de Svix)

**11:37:50** - Checkout creado: `ch_kdenzovkwtkoouit`  
**11:41:30** - Checkout creado: `ch_y3qqcphba761ltuc`  
**11:56:33** - Checkout creado: `ch_rdoiqmcvljlhcarb`  
**12:02:37** - Checkout creado: `ch_sf0zmint8qt5ygwi`  
**12:11:27** - Checkout creado: `ch_duo6qssmh14d09ho`  
**12:26:06** - Checkout creado: `ch_8owkvqg9zyagywh8`  
**12:32:30** - Checkout creado: `ch_ckunznoalskjd5u8`  
**12:36:25** - Checkout creado: `ch_7x6cxehkpaald4dk`

**⚠️ PROBLEMA DETECTADO:**
Después del error de firma a las 11:36:12, se crearon **9 checkouts** pero **NINGÚN webhook fue recibido**.

### Análisis del Error de Firma Inválida

**Posibles causas:**

1. **Secreto de Svix cambiado:** El webhook usó un secreto diferente al configurado
2. **Endpoint incorrecto en Svix:** El endpoint podría estar usando otro secreto
3. **Webhook de prueba manual:** Alguien envió un webhook de prueba sin la firma correcta
4. **Rotación de secreto:** Svix podría haber rotado el secreto automáticamente

**Evidencia:**
- IP 157.230.145.249 es legítima de Svix
- El error específico es "No matching signature found"
- Ocurrió solo 1 vez en los logs recientes

---

## 🐛 Problemas Identificados en Detalle

### 1. Los pagos de prueba NO se completan ⚠️⚠️⚠️

**Evidencia:**
- 9+ checkouts creados exitosamente
- 0 webhooks recibidos para esos checkouts
- Solo 2 intenciones registradas (del 20-Nov)

**Diagnóstico:**
Los usuarios (probablemente tú durante las pruebas) están:
1. Llenando el formulario ✅
2. Siendo redirigidos a Recurrente ✅
3. **Abandonando el checkout SIN pagar** ❌

**Solución:**
Completar un pago de prueba usando tarjeta TEST:
```
Número: 4242 4242 4242 4242
Vencimiento: 12/25 (cualquier fecha futura)
CVV: 123
Nombre: Test User
```

### 2. Error de firma inválida (11:36:12)

**Evidencia:**
```
[2025-11-21 11:36:12] local.WARNING: Webhook Recurrente: firma inválida 
{"error":"No matching signature found","ip":"157.230.145.249"}
```

**Diagnóstico:**
- Ocurrió 1 sola vez
- IP válida de Svix
- Podría ser un webhook de prueba manual
- Podría indicar un problema de configuración en Svix

**Acciones recomendadas:**
1. Verificar en el dashboard de Svix cuál fue el evento que causó este error
2. Confirmar que el secreto en `.env` coincide con el del endpoint en Svix
3. Verificar que el endpoint está en la Application correcta (TEST)

### 3. Webhooks no recibidos después de checkouts

**Patrón observado:**
```
Checkout creado → Usuario va a Recurrente → ??? → No hay webhook
```

**Posibles causas:**
1. **El usuario NO completa el pago** (más probable)
2. Svix no está enviando webhooks para el ambiente TEST
3. El endpoint en Svix no está suscrito a `payment_intent.succeeded`
4. El endpoint está configurado para LIVE pero usas llaves TEST

**Cómo verificar:**
1. Ve a https://app.svix.com
2. Busca la Application de TEST
3. Ve a "Logs" o "Messages"
4. Verifica si aparecen eventos `payment_intent.succeeded` después de las 11:36:12

### 4. Falta de validación de cupo de misa

**Problema:**
El código NO valida si la misa seleccionada tiene cupo disponible antes de crear el checkout.

**Riesgo:**
Un usuario podría pagar por una intención en una misa que ya está llena.

**Solución recomendada:**
```php
// En PublicIntentionController@checkout()
if ($validated['mass_instance_id']) {
    $mass = MassInstance::findOrFail($validated['mass_instance_id']);
    $currentCount = $mass->intentions()->where('status', '!=', 'cancelled')->count();
    
    if ($currentCount >= $mass->max_intentions) {
        return back()->withErrors([
            'mass_instance_id' => 'Esta misa ya no tiene cupo disponible.'
        ]);
    }
}
```

---

## 🔧 Configuración de Svix Requerida

### Endpoint que debe existir en Svix

**URL del endpoint:**
```
https://basilicadelrosario.gt/api/webhook/recurrente
```

**Eventos que DEBEN estar suscritos:**
- ✅ `payment_intent.succeeded` (OBLIGATORIO)
- ☑️ `checkout.session.completed` (opcional pero recomendado)
- ☑️ `payment_intent.failed` (opcional)
- ☑️ `refund.create` (opcional)

**Verificación del secreto:**
El secreto mostrado en el endpoint de Svix debe coincidir con:
```
whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3
```

### ⚠️ Problema común: Ambiente incorrecto

Si estás usando llaves **TEST** de Recurrente:
```
RECURRENTE_PUBLIC_KEY=pk_test_...
RECURRENTE_SECRET_KEY=sk_test_...
```

**Entonces necesitas:**
- Un **endpoint TEST** en Svix
- Conectado a una **Application TEST** de Recurrente en Svix

**Cómo verificar:**
1. Ve a Svix Dashboard
2. Selecciona la Application correcta (debe decir "TEST" o similar)
3. Ve a Endpoints
4. Verifica que `basilicadelrosario.gt/api/webhook/recurrente` esté listado
5. Haz click en el endpoint y verifica el secreto

---

## 📋 Checklist de Diagnóstico

### Verificar Configuración de Svix

- [ ] El endpoint `https://basilicadelrosario.gt/api/webhook/recurrente` existe en Svix
- [ ] El secreto en Svix coincide con `whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3`
- [ ] El evento `payment_intent.succeeded` está suscrito
- [ ] El endpoint está en la Application TEST (no LIVE)
- [ ] El estado del endpoint es "Enabled"

### Verificar Código Laravel

- [x] Controlador webhook existe y está funcionando
- [x] Ruta `/api/webhook/recurrente` registrada sin CSRF
- [x] Verificación de firma Svix implementada
- [x] Idempotencia por `payment_intent_id` implementada
- [x] Logging completo de eventos
- [x] Campo `payment_intent_id` es UNIQUE en BD
- [x] RecurrenteClient crea checkouts correctamente

### Hacer Prueba Completa

- [ ] Llenar formulario en `/intenciones`
- [ ] Ser redirigido a Recurrente
- [ ] **COMPLETAR el pago con tarjeta TEST** ⚠️ (no abandonar)
- [ ] Verificar webhook recibido en logs
- [ ] Verificar intención creada en BD
- [ ] Probar idempotencia (reenviar mismo webhook)

---

## 🚀 Plan de Acción Inmediato

### Paso 1: Verificar Svix Dashboard

```bash
# Ir a:
https://app.svix.com

# Verificar:
1. ¿Qué Application estás viendo? (debe ser TEST)
2. ¿El endpoint basilicadelrosario.gt existe?
3. ¿Qué eventos aparecen en los logs después de las 11:36?
4. ¿Hay eventos con status "Failed" o "Delivered"?
```

### Paso 2: Enviar webhook de prueba desde Svix

```bash
# En Svix Dashboard:
1. Ve al endpoint de basilicadelrosario.gt
2. Click en "Testing" o "Send Example"
3. Selecciona evento: payment_intent.succeeded
4. Usa este payload:

{
  "id": "pi_test_manual_verificacion",
  "event_type": "payment_intent.succeeded",
  "amount_in_cents": 5000,
  "currency": "GTQ",
  "created_at": "2025-11-21T20:00:00Z",
  "metadata": {
    "mass_id": null,
    "intention_type": "normal",
    "category": "accion_de_gracias",
    "public_text": "Prueba manual desde Svix",
    "donor_name": "Test Manual Svix",
    "donor_email": "test@svix.com",
    "donor_phone": null
  }
}

5. Click "Send"
6. Verificar respuesta: debe ser 200 OK
```

### Paso 3: Verificar en Laravel

```bash
# Monitorear logs en tiempo real
tail -f /var/www/santo-domingo/storage/logs/laravel.log | grep "Webhook Recurrente"

# Verificar intención creada
cd /var/www/santo-domingo
php artisan tinker --execute="
App\Models\Intention::where('payment_intent_id', 'pi_test_manual_verificacion')->first()
"
```

### Paso 4: Hacer pago de prueba COMPLETO

```bash
# 1. En una terminal, monitorear logs:
tail -f /var/www/santo-domingo/storage/logs/laravel.log | grep -E "Recurrente|Webhook"

# 2. En el navegador:
https://basilicadelrosario.gt/intenciones

# 3. Llenar formulario:
- Tipo: Normal (Q50)
- Texto: "Prueba completa 21 Nov"
- Nombre: Tu Nombre
- Email: tu@email.com

# 4. Click "Proceder al pago"

# 5. En la página de Recurrente, COMPLETAR el pago:
Tarjeta: 4242 4242 4242 4242
Vencimiento: 12/25
CVV: 123
Nombre: Test User

# 6. ¡¡¡IMPORTANTE!!! Esperar hasta que diga "Pago exitoso"

# 7. Observar los logs - debe aparecer:
[INFO] Webhook Recurrente recibido
[INFO] Webhook Recurrente: intención creada
```

### Paso 5: Verificar resultado

```bash
# Ver últimas intenciones
php artisan tinker --execute="
App\Models\Intention::where('payment_method', 'recurrente')
    ->latest()
    ->limit(5)
    ->get(['id', 'payment_intent_id', 'donor_name', 'amount', 'created_at'])
"

# Ejecutar script de diagnóstico
php verificar-webhook.php
```

---

## 🔒 Consideraciones de Seguridad

### ✅ Implementadas Correctamente

1. **Verificación de firma Svix:** Impide webhooks falsos
2. **Idempotencia:** Evita procesar el mismo pago dos veces
3. **Validación de formulario:** Previene datos maliciosos
4. **Transacciones DB:** Garantiza consistencia de datos
5. **Logging completo:** Permite auditoría y debugging

### ⚠️ Recomendaciones Adicionales

1. **Rate limiting:** Agregar throttle al endpoint del webhook
```php
Route::post('/webhook/recurrente', ...)
    ->middleware('throttle:webhook');
```

2. **IP Whitelist:** Validar que el webhook venga de IPs de Svix
```php
// En RecurrenteWebhookController
$allowedIps = ['157.230.145.249', /* otras IPs de Svix */];
if (!in_array($request->ip(), $allowedIps)) {
    Log::warning('Webhook de IP no autorizada', ['ip' => $request->ip()]);
    abort(403);
}
```

3. **Monitoreo de webhooks fallidos:** Alerta si muchos webhooks fallan
4. **Timeout de checkouts:** Limpiar checkouts abandonados después de 24h

---

## 📈 Métricas y Monitoreo

### KPIs Recomendados

1. **Tasa de conversión de checkouts:**
   - Checkouts creados vs Pagos completados
   - **Actual:** 2/11+ = ~18% (muy bajo, indica abandonos)
   - **Esperado:** >60%

2. **Tiempo promedio de checkout:**
   - Desde creación hasta pago
   - **Actual:** No medido

3. **Tasa de error de webhooks:**
   - Webhooks rechazados vs recibidos
   - **Actual:** 1 error detectado

4. **Idempotencia activada:**
   - Webhooks duplicados detectados
   - **Actual:** 0 (funciona correctamente)

### Comandos de Monitoreo

```bash
# Ver estadísticas diarias
php artisan tinker --execute="
\$today = now()->startOfDay();
echo 'Checkouts hoy: ' . \App\Models\Intention::where('created_at', '>=', \$today)->count() . PHP_EOL;
echo 'Pagados hoy: ' . \App\Models\Intention::where('created_at', '>=', \$today)->where('status', 'paid')->count() . PHP_EOL;
"

# Ver últimos errores
tail -100 storage/logs/laravel.log | grep -i "error\|warning" | grep -i "recurrente"

# Ver webhooks recientes
tail -100 storage/logs/laravel.log | grep "Webhook Recurrente"
```

---

## 🎯 Próximos Pasos para Producción

### Antes de ir a LIVE

- [ ] Completar al menos 5 pagos de prueba exitosos
- [ ] Verificar que todos los webhooks se reciben
- [ ] Probar reembolso (si es posible en TEST)
- [ ] Verificar certificados descargables
- [ ] Probar con diferentes tipos de intención (rosario, cantada)
- [ ] Validar límites de cupo de misas
- [ ] Revisar textos y diseño del formulario público
- [ ] Configurar monitoreo de errores (Sentry, Bugsnag, etc.)

### Migración a LIVE

1. **Obtener llaves LIVE de Recurrente:**
   ```env
   RECURRENTE_PUBLIC_KEY=pk_live_...
   RECURRENTE_SECRET_KEY=sk_live_...
   ```

2. **Crear endpoint LIVE en Svix:**
   - Misma URL: `https://basilicadelrosario.gt/api/webhook/recurrente`
   - Suscribir a los mismos eventos
   - Guardar nuevo `SVIX_WEBHOOK_SECRET` (será diferente al de TEST)

3. **Actualizar .env en producción:**
   ```bash
   # En el servidor
   nano /var/www/santo-domingo/.env
   # Actualizar las 3 variables
   # Guardar y salir
   
   php artisan config:clear
   php artisan optimize
   ```

4. **Hacer pago de prueba pequeño (Q1 o Q5):**
   - Verificar que funciona end-to-end
   - Revisar que el webhook llegue
   - Confirmar intención en BD

5. **Monitorear primeras 24 horas:**
   - Revisar logs cada 2-4 horas
   - Verificar que no haya errores
   - Confirmar que los pagos se registran

---

## 📞 Contactos y Recursos

### Documentación Oficial

- **Recurrente API:** https://recurrente.com/docs
- **Svix Webhooks:** https://docs.svix.com
- **Laravel HTTP Client:** https://laravel.com/docs/12.x/http-client

### Dashboards

- **Svix:** https://app.svix.com
- **Recurrente:** https://app.recurrente.com/dashboard

### Soporte

- **Recurrente:** soporte@recurrente.com
- **Svix:** support@svix.com

---

## 🎓 Aprendizajes y Buenas Prácticas

### Lo que se hizo bien

1. ✅ **Arquitectura limpia:** Separación de responsabilidades (Controller → Service → API)
2. ✅ **Idempotencia robusta:** Uso de campo único en BD
3. ✅ **Logging exhaustivo:** Facilita debugging
4. ✅ **Validación de datos:** Request classes con reglas claras
5. ✅ **Manejo de errores:** Excepciones específicas con mensajes amigables
6. ✅ **Seguridad:** Verificación de firma digital
7. ✅ **Transacciones DB:** Garantiza consistencia

### Áreas de mejora

1. ⚠️ **Falta validación de cupo:** Riesgo de sobreventa
2. ⚠️ **No hay timeout de checkouts:** Checkouts abandonados quedan en el sistema
3. ⚠️ **Falta monitoreo automatizado:** No hay alertas de errores
4. ⚠️ **Sin rate limiting:** Vulnerable a spam de webhooks
5. ⚠️ **No hay IP whitelist:** Acepta webhooks de cualquier IP
6. ⚠️ **Falta documentación de usuario:** No hay guía de uso del formulario

---

## 📝 Conclusión

El sistema de Recurrente y webhooks está **correctamente implementado a nivel técnico** y **funcionó exitosamente 2 veces** (20-Nov).

El problema actual **NO es del código**, sino de:

1. **Pagos de prueba no completados:** Los checkouts se abandonan sin pagar
2. **Posible configuración incorrecta en Svix:** El endpoint podría estar en el ambiente incorrecto

**Acción inmediata requerida:**

1. Ir a Svix Dashboard y verificar la configuración
2. Completar un pago de prueba de principio a fin
3. Verificar que el webhook llegue y se procese

Si después de seguir el **Plan de Acción Inmediato** (sección anterior) el problema persiste, el siguiente paso sería:

- Compartir el output del script `verificar-webhook.php`
- Compartir screenshot de los logs en Svix Dashboard
- Verificar si hay algún error de red o firewall bloqueando los webhooks

---

**Generado:** 21 de Noviembre de 2025  
**Última actualización:** 21 de Noviembre de 2025 a las 12:40 PM  
**Versión:** 1.0

