# 🔧 Diagnóstico: Webhook de Recurrente Guatemala

## ✅ ESTADO ACTUAL (21 Nov 2025, 12:30 PM)

**El webhook está configurado correctamente y funcionando.** Se han registrado **2 intenciones** desde Recurrente:

1. **ID 42** - Carlos Rodríguez - Q150.00 (20 Nov, 16:34)
2. **ID 41** - María González - Q50.00 (20 Nov, 16:16)

## 🔍 Problema Identificado

**Último error:** 21 Nov 2025, 11:36:12 AM
```
[2025-11-21 11:36:12] local.WARNING: Webhook Recurrente: firma inválida 
{"error":"No matching signature found","ip":"157.230.145.249"}
```

**Situación observada:**
- Después del error, se crearon varios checkouts (11:37, 11:41, 11:56, 12:02, 12:11)
- **NO hay logs de webhooks recibidos** para esos checkouts
- Esto sugiere que los pagos **no se completaron** o Svix **no está enviando** los webhooks

**Posibles causas:**
1. Los checkouts se abandonaron sin completar el pago
2. El endpoint en Svix NO está configurado para el ambiente correcto (TEST vs LIVE)
3. Los eventos no están suscritos correctamente en Svix

## ✅ Pasos para Verificar y Solucionar

### 1. Verificar que el secreto de Svix sea el correcto

Tu secreto actual: `whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3`

**Acción:**
1. Ve a **https://app.svix.com**
2. Selecciona tu **Application** (debería ser "Recurrente Guatemala TEST" o similar)
3. Ve a **Endpoints**
4. Busca: `https://basilicadelrosario.gt/api/webhook/recurrente`
5. Verifica que el **Signing Secret** coincida con `whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3`

### 2. Verificar que el endpoint esté en el ambiente correcto

Estás usando llaves TEST de Recurrente:
- `RECURRENTE_PUBLIC_KEY=pk_test_CpGa8YL0Dti1...`

Por lo tanto, necesitas un **endpoint TEST en Svix**.

**Verificar:**
1. En Svix, asegúrate de estar en la **Application de TEST**
2. Si tienes múltiples Applications, verifica que estés viendo la correcta
3. El endpoint debe estar en la misma Application que recibe los eventos de TEST

### 3. Verificar eventos suscritos

En el endpoint de Svix, verifica que estén suscritos estos eventos:
- ✅ `payment_intent.succeeded` (OBLIGATORIO)
- ☑️ `checkout.session.completed` (opcional pero recomendado)
- ☑️ `payment_intent.failed` (opcional, para logs)

**Cómo verificar:**
1. En el endpoint, ve a la pestaña **"Message Filters"** o **"Events"**
2. Asegúrate que `payment_intent.succeeded` esté seleccionado

### 4. Hacer un pago de prueba COMPLETO

Los checkouts que creaste después no tienen webhooks porque probablemente **no completaste el pago**.

**Pasos:**
1. Ve a https://basilicadelrosario.gt/intenciones
2. Llena el formulario
3. **IMPORTANTE:** Completa el pago con tarjeta de prueba:
   - Número: `4242 4242 4242 4242`
   - Vencimiento: `12/25` (cualquier fecha futura)
   - CVV: `123`
   - Nombre: Cualquier nombre
4. **Espera a que aparezca "Pago exitoso"**
5. Ve inmediatamente a revisar los logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "Webhook Recurrente"
   ```

### 5. Verificar en el dashboard de Svix

Después de completar un pago de prueba:

1. Ve a Svix → tu Application → **Logs** o **Messages**
2. Deberías ver un evento `payment_intent.succeeded`
3. Verifica su estado:
   - ✅ **Delivered** (200) = El webhook funcionó
   - ❌ **Failed** (400/500) = Hubo un error
4. Si falló, haz click para ver el error exacto

## 🧪 Método de Prueba Rápido desde Svix

En lugar de hacer un pago real, puedes **enviar un webhook de prueba** desde Svix:

1. Ve al endpoint en Svix
2. Click en **"Testing"** o **"Send Example"**
3. Selecciona el evento: `payment_intent.succeeded`
4. Modifica el payload de prueba para incluir la metadata:
   ```json
   {
     "id": "pi_test_manual_" + Date.now(),
     "event_type": "payment_intent.succeeded",
     "amount_in_cents": 5000,
     "currency": "GTQ",
     "created_at": "2025-11-21T18:30:00Z",
     "metadata": {
       "intention_type": "normal",
       "category": "accion_de_gracias",
       "public_text": "Prueba desde Svix",
       "donor_name": "Test Svix",
       "donor_email": "test@svix.com"
     }
   }
   ```
5. Click **"Send"**
6. Verifica que aparezca como **"Delivered"** (200)
7. Revisa que se haya creado en la BD:
   ```bash
   php artisan tinker --execute="App\Models\Intention::latest()->first()"
   ```

## 🔍 Comandos de Diagnóstico

Ejecuta estos comandos para verificar el estado:

```bash
# Ver estado completo
php verificar-webhook.php

# Diagnóstico detallado
php artisan webhook:diagnosticar

# Ver últimos logs
tail -50 storage/logs/laravel.log | grep "Webhook"

# Ver intenciones de Recurrente
php artisan tinker --execute="App\Models\Intention::where('payment_method', 'recurrente')->get()"
```

## 📝 Configuración Actual

```
URL del Webhook: https://basilicadelrosario.gt/api/webhook/recurrente
Ambiente: TEST
IP de Svix: 157.230.145.249
```

## 🎯 Checklist de Verificación

- [ ] Secreto de Svix actualizado en `.env`
- [ ] Cache limpiado (`php artisan config:clear`)
- [ ] Webhook de prueba enviado desde Svix
- [ ] Webhook muestra status 200 en Svix
- [ ] Log muestra "Webhook Recurrente: intención creada"
- [ ] Se creó un registro en la tabla `intentions`
- [ ] El registro tiene `payment_method = 'recurrente'`

## 🆘 Ayuda Adicional

Si después de estos pasos el problema persiste:

1. Comparte el output completo de: `php artisan webhook:diagnosticar`
2. Comparte los últimos logs: `tail -50 storage/logs/laravel.log`
3. Verifica en Svix si el webhook aparece como "Failed" o "Delivered"
