# 🔧 Configurar Webhook de Recurrente

## ✅ CÓMO FUNCIONA

**Recurrente usa Svix** para gestionar webhooks. La configuración se hace desde el dashboard web de Recurrente, que te redirige automáticamente a Svix.

**NO necesitas hacer peticiones a `/api/webhooks`** - ese endpoint es solo para proveedores de facturación electrónica.

---

## 📋 PASOS PARA CONFIGURAR EL WEBHOOK

### 1. Accede a tu cuenta de Recurrente

Ve a: **https://app.recurrente.com/**

Inicia sesión con tu cuenta.

### 2. Ve a Webhooks

Navega a: **Mi Cuenta → Desarrolladores y API → Webhooks**

Esto te redirigirá automáticamente a **Svix** (la plataforma que gestiona los webhooks).

### 3. Agrega el Endpoint en Svix

En Svix, haz clic en **"Add Endpoint"**

Completa:

**Endpoint URL:**
```
https://basilicadelrosario.gt/api/webhook/recurrente
```

**Description (opcional):**
```
Webhook para intenciones de misa - Parroquia Santo Domingo
```

**Event Types** (selecciona estos eventos):
- ✅ `payment_intent.succeeded` - Cuando un pago es exitoso
- ✅ `payment_intent.failed` - Cuando un pago falla
- ✅ `refund.create` - Cuando se crea un reembolso

Haz clic en **"Create"**

### 4. Copia el Signing Secret

Después de crear el endpoint, Svix te mostrará un **Signing Secret** (comienza con `whsec_...`).

**IMPORTANTE:** Este secret debe estar en tu `.env`:

```bash
SVIX_WEBHOOK_SECRET=whsec_Ynyjo3l+kVJsTyK1gsjevKPu0VQPk1I3
```

Si necesitas aceptar **más de un secret** (por ejemplo, uno LIVE y otro TEST para pruebas de punta a punta), agrega la lista adicional separada por comas:

```bash
SVIX_WEBHOOK_SECRETS=whsec_live_xxxxx,whsec_test_yyyyy
```

La aplicación intentará verificar la firma usando cualquiera de los secretos configurados.

Si el secret mostrado en Svix es diferente, actualiza tu `.env` con el nuevo valor y reinicia tu servidor.

---

## 🧪 PRUEBA QUE FUNCIONA

### Opción 1: Desde Svix (Recomendado)

1. En Svix, haz clic en tu endpoint
2. Ve a la pestaña **"Testing"**
3. Selecciona el evento: `payment_intent.succeeded`
4. Haz clic en **"Send Example"**
5. Deberías ver respuesta **HTTP 200**

Luego verifica los logs:

```bash
cd /var/www/santo-domingo
tail -30 storage/logs/laravel.log | grep WEBHOOK
```

Deberías ver logs con emojis como:
```
🚀 [WEBHOOK INICIO] Request recibido
✅ [WEBHOOK] Firma Svix verificada correctamente
💰 [WEBHOOK] Procesando pago exitoso...
🎉 [SUCCESS] Intención creada exitosamente
```

### Opción 2: Pago de Prueba Real

1. Ve a: https://basilicadelrosario.gt/intenciones
2. Completa el formulario
3. Usa tarjeta de prueba:
   - **Número:** 4242 4242 4242 4242
   - **Vencimiento:** 12/30
   - **CVV:** 123
4. Completa el pago
5. Verifica que te redirija a la página de éxito
6. Revisa los logs (debe aparecer el webhook en segundos)

---

## 🔍 VERIFICAR EN LA BASE DE DATOS

Después de recibir un webhook, verifica que se creó la intención:

```bash
cd /var/www/santo-domingo
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

echo \"📊 ÚLTIMAS INTENCIONES DE RECURRENTE:\n\n\";
\$intentions = App\Models\Intention::where('payment_method', 'recurrente')
    ->latest('id')
    ->limit(5)
    ->get();

foreach (\$intentions as \$i) {
    echo \"ID: \" . \$i->id . \" - \" . \$i->donor_name . \" - Q\" . \$i->amount . \" - \" . \$i->created_at . \"\n\";
}
"
```

---

## 🔧 VERIFICAR ESTADO DEL ENDPOINT EN SVIX

En el dashboard de Svix, verifica que tu endpoint:

- ✅ Esté **Enabled** (habilitado)
- ✅ NO esté **Paused** (pausado)
- ✅ NO tenga errores recientes
- ✅ Muestre **"Delivery Stats"** con mensajes enviados

Si está pausado o deshabilitado:
1. Haz clic en el endpoint
2. Haz clic en **"Enable"** o **"Resume"**

---

## ⚠️ SOLUCIÓN DE PROBLEMAS

### El webhook no llega después de un pago

**Verifica en Svix:**
1. Ve a **"Logs"** o **"Messages"**
2. Busca eventos recientes
3. Si ves eventos con **estado 4xx o 5xx**, revisa el error

**Verifica en tus logs de Laravel:**
```bash
tail -100 storage/logs/laravel.log | grep ERROR
```

### El endpoint está pausado en Svix

**Causa:** Svix pausa endpoints automáticamente después de muchos errores.

**Solución:**
1. Corrige el error en tu código
2. En Svix, haz clic en **"Resume"**

### Los eventos no aparecen en Svix

**Causa:** Recurrente no está enviando eventos a Svix.

**Verificar:**
1. ¿Completaste el pago correctamente?
2. ¿Usaste las tarjetas de prueba correctas?
3. ¿Estás en modo TEST?

Si nada de esto funciona, contacta a soporte de Recurrente.

---

## ✅ CHECKLIST Final

- [ ] Configurar endpoint en Recurrente dashboard
- [ ] Verificar que el signing secret coincida
- [ ] Probar con un pago de prueba
- [ ] Verificar logs con emojis 🎉
- [ ] Verificar BD que se creó la intención
- [ ] Celebrar 🎊

---

**Última actualización:** 24 de noviembre de 2025
