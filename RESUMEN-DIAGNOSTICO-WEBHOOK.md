# 📊 Resumen del Diagnóstico - Webhook Recurrente Guatemala

**Fecha:** 21 de Noviembre de 2025, 12:30 PM
**Estado:** ✅ WEBHOOK FUNCIONANDO (con 1 error reciente a investigar)

---

## ✅ Lo que SÍ funciona

1. **Webhook configurado correctamente:**
   - Ruta: `/api/webhook/recurrente` ✅
   - Controller: `RecurrenteWebhookController` ✅
   - Verificación Svix implementada ✅
   - Idempotencia por `payment_intent_id` ✅

2. **Secreto de Svix correcto:**
   - Configurado: `whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3` ✅
   - Coincide con el mostrado en captura de pantalla ✅

3. **Intenciones registradas en BD:**
   - **2 intenciones** creadas exitosamente desde Recurrente
   - ID 42: Carlos Rodríguez - Q150 (20 Nov 16:34)
   - ID 41: María González - Q50 (20 Nov 16:16)

---

## ⚠️ Punto a Investigar

**Último error:** 21 Nov 2025, 11:36:12 AM
```
[2025-11-21 11:36:12] local.WARNING: Webhook Recurrente: firma inválida 
{"error":"No matching signature found","ip":"157.230.145.249"}
```

**IP origen:** 157.230.145.249 (IP de Svix)

### Observaciones:

Después del error a las 11:36:12, se crearon varios checkouts:
- 11:37:50 → `ch_kdenzovkwtkoouit`
- 11:41:30 → `ch_y3qqcphba761ltuc`
- 11:56:33 → `ch_rdoiqmcvljlhcarb`
- 12:02:37 → `ch_sf0zmint8qt5ygwi`
- 12:11:27 → `ch_duo6qssmh14d09ho`

**Pero NO hay logs de webhooks recibidos para estos checkouts.**

### Posibles causas:

1. ❓ **Los pagos NO se completaron** (se abandonó el checkout)
2. ❓ **Svix NO está enviando webhooks** cuando se completa el pago
3. ❓ **El endpoint de Svix no está en el ambiente correcto** (TEST vs LIVE)

---

## 🔍 Próximos pasos recomendados

### OPCIÓN 1: Verificar en Svix (MÁS RÁPIDO)

1. Ve a https://app.svix.com
2. Revisa los **Logs/Messages** de tu Application
3. Busca webhooks enviados después de las 11:36:12
4. Verifica si aparecen como:
   - ✅ **Delivered** (200) → Todo bien
   - ❌ **Failed** → Ver error específico
   - ❓ **No aparecen** → Los pagos no se completaron

### OPCIÓN 2: Hacer un pago de prueba COMPLETO (RECOMENDADO)

```bash
# 1. Monitorear logs en tiempo real
tail -f /var/www/santo-domingo/storage/logs/laravel.log | grep "Webhook\|Recurrente"
```

**Luego:**
1. Ve a https://basilicadelrosario.gt/intenciones
2. Llena el formulario
3. Completa el pago con tarjeta de prueba:
   - **Número:** 4242 4242 4242 4242
   - **Vencimiento:** 12/25
   - **CVV:** 123
4. **IMPORTANTE:** Espera hasta que diga "Pago exitoso"
5. Observa si aparece el log del webhook

### OPCIÓN 3: Enviar webhook de TEST desde Svix

1. En Svix → Endpoint → **Testing**
2. Enviar evento: `payment_intent.succeeded`
3. Usar este payload:
```json
{
  "id": "pi_test_manual_12345",
  "event_type": "payment_intent.succeeded",
  "amount_in_cents": 5000,
  "currency": "GTQ",
  "created_at": "2025-11-21T18:30:00Z",
  "metadata": {
    "intention_type": "normal",
    "category": "accion_de_gracias",
    "public_text": "Test desde Svix dashboard",
    "donor_name": "Prueba Manual",
    "donor_email": "test@svix.com"
  }
}
```

4. Verificar que regrese **200 OK**
5. Verificar en BD:
```bash
php verificar-webhook.php
```

---

## 📋 Comandos Útiles

```bash
# Ver estado general
php verificar-webhook.php

# Diagnóstico completo
php artisan webhook:diagnosticar

# Ver últimos webhooks en logs
tail -100 storage/logs/laravel.log | grep "Webhook Recurrente"

# Ver intenciones de Recurrente en BD
php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); App\Models\Intention::where('payment_method', 'recurrente')->get()->each(fn(\$i) => print_r(\$i->toArray()));"

# Monitorear logs en tiempo real
tail -f storage/logs/laravel.log | grep "Webhook\|Recurrente"
```

---

## 💡 Conclusión

El webhook **SÍ está funcionando** (evidencia: 2 intenciones ya registradas).

El error del 21 Nov a las 11:36:12 fue **un caso aislado** que necesita investigación.

**Recomendación:** Hacer un pago de prueba completo **ahora mismo** mientras monitoreas los logs para confirmar que todo funciona correctamente.

---

## 📞 Si necesitas ayuda adicional

1. Comparte el output de: `php verificar-webhook.php`
2. Comparte captura de los logs en Svix (Messages/Logs section)
3. Indica si completaste algún pago después de las 11:36 AM
