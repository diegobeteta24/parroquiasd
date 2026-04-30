# Auditoría técnica para formulario de implementación/certificación NeoPay (NeoNet)

Fecha de auditoría: 2026-03-14  
Proyecto auditado: `santo-domingo` (Laravel)

## Tabla de validación de campos

| Campo del formulario | Respuesta recomendada | Nivel de confianza | Evidencia encontrada en el proyecto | Explicación técnica breve | ¿Debo confirmar algo externamente? | Qué debo preguntar si falta información |
|---|---|---|---|---|---|---|
| Datos generales del comercio | **Nombre comercial:** Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo. **Sitio principal:** https://basilicadelrosario.gt | Alta | [config/portal.php](config/portal.php), [README.md](README.md#L20), [.env](.env#L6) | El nombre institucional está configurado en portal y el dominio principal aparece en APP_URL y documentación de despliegue. | No | N/A |
| ¿Posee software de comercio electrónico? (SI/NO) | **SI** | Alta | [routes/web.php](routes/web.php#L22-L25), [app/Http/Controllers/PublicIntentionController.php](app/Http/Controllers/PublicIntentionController.php), [app/Services/RecurrenteClient.php](app/Services/RecurrenteClient.php) | Existe flujo de checkout en línea para cobros de intenciones (`/intenciones/checkout`) con redirección a pasarela y confirmación de pago. | No | N/A |
| Nombre / proveedor del software | **Software propio (desarrollo a medida) sobre Laravel 12 + integración de pasarela externa**. Proveedor de framework: Laravel. | Media | [composer.json](composer.json), [README.md](README.md), estructura `app/` con controladores/servicios propios | Es una app custom (no WooCommerce/Shopify/Magento). Framework y librerías sí son de terceros. | Sí | Confirmar razón social/proveedor que figurará legalmente como desarrollador e integrador. |
| Soporte del software: propio o contratado | **PROBABLE: Propio** (equipo interno o desarrollador del proyecto) | Baja | Código y documentación operativa dentro del mismo repo; no se identifica contrato/SLA en código | El repositorio no contiene un documento contractual de soporte; técnicamente luce mantenido por equipo del proyecto. | Sí | ¿Quién brinda soporte formal 24/7 o en horario laboral? ¿Existe proveedor externo con SLA? |
| Plataforma tecnológica | **Laravel 12 (PHP 8.2+), Livewire, Jetstream, Vite/Tailwind, MySQL/SQL compatible** | Alta | [composer.json](composer.json), [package.json](package.json), [bootstrap/app.php](bootstrap/app.php), [phpunit.xml](phpunit.xml) | Dependencias y estructura confirman stack web moderno en PHP. | No | N/A |
| Herramienta de desarrollo: PHP / Java / .NET / ASP / Otro | **PHP** (principal), con frontend JS (Vite/Tailwind/Alpine) | Alta | [composer.json](composer.json), [package.json](package.json), [artisan](artisan) | Backend y negocio están implementados en PHP/Laravel. | No | N/A |
| Tipo de integración | **API Restful** | Alta | [app/Services/RecurrenteClient.php](app/Services/RecurrenteClient.php), [routes/api.php](routes/api.php#L11-L12), [app/Http/Controllers/Webhook/RecurrenteWebhookController.php](app/Http/Controllers/Webhook/RecurrenteWebhookController.php) | Consume API HTTP JSON de pasarela y recibe webhooks REST por endpoint API. | No | N/A |
| Integración API Restful y servicios relacionados | **Sí: cliente HTTP saliente + webhook entrante + firma Svix**. **Inconsistencia:** actualmente implementado para Recurrente, no NeoPay. | Alta | [config/services.php](config/services.php#L38-L52), [app/Services/RecurrenteClient.php](app/Services/RecurrenteClient.php), [routes/api.php](routes/api.php#L11-L12) | El patrón de integración REST está claro; pero la pasarela codificada es Recurrente/Svix. | Sí | Solicitar a NeoPay endpoints, esquema de auth, firma de webhook, ambientes TEST/LIVE e IPs de origen permitidas. |
| Servicio de hosting: propio o contratado | **NO VERIFICABLE DESDE EL CÓDIGO** (PROBABLE: contratado) | Baja | Referencias documentales a infraestructura externa en [EMAIL-BACKUP-RELAY.md](EMAIL-BACKUP-RELAY.md) (DigitalOcean/Hostinger) | No hay inventario oficial de activos ni contrato de hosting dentro del repo. | Sí | ¿El hosting productivo es VPS propio, cloud administrado o plan contratado? ¿Proveedor exacto? |
| Hosting propio o contratado | **REQUIERE VALIDACIÓN EXTERNA** | Baja | No existe archivo de infraestructura definitiva (Terraform, panel export, contrato) | Campo administrativo/contractual no demostrable solo con código fuente. | Sí | ¿Titular de la cuenta de hosting? ¿Proveedor? ¿plan? ¿entornos separados? |
| Servidor y hosting (tecnología web server) | **PROBABLE: Nginx + PHP-FPM** | Media | [README.md](README.md#L18-L44) | README incluye bloque de server Nginx con `fastcgi_pass` a socket PHP-FPM en ruta Linux. | Sí | ¿En producción usan Nginx o Apache? ¿Hay balanceador/reverse proxy/WAF delante? |
| Sistema operativo del servidor: Windows / Linux / Otro | **PROBABLE: Linux** | Media | [README.md](README.md#L20-L44), rutas tipo `/var/www/...`, socket unix `/run/php/...` | Evidencia técnica apunta a Linux; no hay inventario formal del servidor productivo. | Sí | Confirmar distro/version (Ubuntu/Debian/etc.) para prueba y producción. |
| ¿Tiene certificado de seguridad? (SI/NO) | **SI (PROBABLE, NO CONFIRMADO)** | Media | APP_URL usa `https` en [.env](.env#L6), documentación usa URLs HTTPS en [CONFIGURAR-WEBHOOK-RECURRENTE.md](CONFIGURAR-WEBHOOK-RECURRENTE.md) | El proyecto opera con URLs HTTPS, pero el tipo exacto del certificado no está en el repo. | Sí | ¿Qué certificado SSL/TLS está activo (emisor, vigencia, CN/SAN), y dónde termina TLS? |
| Método de encriptación: 128b / 192b / 256b / 512b / 1024b | **NO VERIFICABLE DESDE EL CÓDIGO** | Baja | No hay configuración TLS/cipher suite del servidor en repo | El cifrado depende de configuración runtime (Nginx/Apache/LB/CDN). | Sí | ¿Qué versión TLS y cipher suite negocia el dominio? (ej. TLS 1.2/1.3 con AES-256-GCM). |
| Dirección IP pública del servidor de pruebas | **REQUIERE VALIDACIÓN EXTERNA** | Baja | No hay IP de servidor en código; solo aparece IP de origen Svix en documentación histórica (no corresponde al servidor propio) | La IP pública depende de red/hosting y puede estar detrás de CDN o proxy. | Sí | ¿Cuál es la IP pública (egress/ingress) del ambiente de pruebas? ¿Es fija? |
| URL del sitio de pruebas (https://) | **NO VERIFICABLE DESDE EL CÓDIGO** | Baja | Se observa dominio principal en producción, pero no dominio staging explícito | No aparece variable/archivo inequívoco de entorno de pruebas con URL propia. | Sí | ¿Existe subdominio de pruebas (ej. `staging...`) con HTTPS funcional? |
| Dirección IP pública del servidor de producción | **REQUIERE VALIDACIÓN EXTERNA** | Baja | No hay inventario de red ni DNS final en el repo | Solo infraestructura externa puede confirmar IP real detrás de proxy/CDN. | Sí | ¿IP pública de producción? ¿usa Cloudflare o balanceador que oculta IP origen? |
| URL del sitio de producción (https://) | **https://basilicadelrosario.gt** | Alta | [.env](.env#L6), [README.md](README.md#L20), [CONFIGURAR-WEBHOOK-RECURRENTE.md](CONFIGURAR-WEBHOOK-RECURRENTE.md#L33) | Dominio productivo consistente en config y documentación operativa. | Sí | Confirmar si también aplica `https://www.basilicadelrosario.gt` como URL oficial en formulario. |
| ¿Se conocen/aplican prácticas OWASP Top 10? (SI/NO) | **SI (con alcance parcial demostrado)** | Media | Validaciones en [app/Http/Requests/CreateIntentionCheckoutRequest.php](app/Http/Requests/CreateIntentionCheckoutRequest.php), auth/roles en [routes/web.php](routes/web.php#L44-L98), rate limiting en [app/Http/Controllers/ContactController.php](app/Http/Controllers/ContactController.php), verificación de firma en [app/Http/Controllers/Webhook/RecurrenteWebhookController.php](app/Http/Controllers/Webhook/RecurrenteWebhookController.php), Fortify/2FA en [config/fortify.php](config/fortify.php) | Hay controles claros de validación, autenticación, autorización, limitación de intentos y verificación criptográfica de webhook. No equivale a certificación OWASP formal completa. | Sí | ¿Tienen política/documentación formal OWASP, pentest reciente o ASVS/SAST/DAST para adjuntar? |
| Página web / URLs de prueba y producción | **Producción:** https://basilicadelrosario.gt. **Pruebas:** REQUIERE VALIDACIÓN EXTERNA | Media | [.env](.env#L6), [README.md](README.md#L20), ausencia de dominio staging explícito | Producción sí está clara; pruebas no aparece definida en configuración versionada. | Sí | Definir URL de TEST y si está protegida por basic auth/VPN. |
| Certificado de seguridad (detalle operativo) | **REQUIERE VALIDACIÓN EXTERNA** | Baja | No hay archivos de certificados ni configuración TLS en repo | La terminación TLS puede estar en Nginx, Load Balancer o CDN fuera del código. | Sí | Emisor, cadena, vigencia, renovación automática, y evidencias de forzado HTTPS/HSTS. |
| Estado de migración a NeoPay/NeoNet | **INCONSISTENCIA CRÍTICA: aún no implementado en código** | Alta | Variables y servicios `RECURRENTE_*` en [.env.example](.env.example), [config/services.php](config/services.php#L44-L52), [app/Services/RecurrenteClient.php](app/Services/RecurrenteClient.php), rutas webhook `recurrente` en [routes/api.php](routes/api.php#L11-L12) | El repositorio actual integra Recurrente; no hay clases, rutas o configs de NeoPay/NeoNet detectables. | Sí | Pedir especificación técnica de NeoPay y definir plan de sustitución (API, webhooks, firma, credenciales, sandbox/live). |

## A. Respuesta final lista para copiar al formulario

> Usa esta versión si el formulario no permite observaciones largas. En campos inciertos, deja constancia de validación externa.

- ¿Posee software de comercio electrónico?: **SI**
- Nombre / proveedor del software: **Desarrollo propio a medida sobre Laravel (PHP) con integración de pasarela de pagos**
- Soporte del software: **PROBABLE: Propio (confirmar administrativamente)**
- Herramienta de desarrollo: **PHP (Laravel 12)**
- Servicio de hosting: **REQUIERE VALIDACIÓN EXTERNA (probable contratado)**
- Sistema operativo del servidor: **PROBABLE: Linux (confirmar proveedor)**
- ¿Tiene certificado de seguridad?: **SI (HTTPS en producción; confirmar detalle SSL/TLS)**
- Método de encriptación: **NO VERIFICABLE DESDE EL CÓDIGO (requiere dato del servidor/CDN)**
- Dirección IP pública del servidor de pruebas: **REQUIERE VALIDACIÓN EXTERNA**
- URL del sitio de pruebas (https://): **REQUIERE VALIDACIÓN EXTERNA**
- Dirección IP pública del servidor de producción: **REQUIERE VALIDACIÓN EXTERNA**
- URL del sitio de producción (https://): **https://basilicadelrosario.gt**
- ¿Se conocen/aplican prácticas OWASP Top 10?: **SI (controles parciales evidenciados en código; sin certificación formal adjunta)**
- Tipo de integración: **API Restful**
- Integración API Restful y servicios relacionados: **Cliente HTTP + Webhook firmado (actualmente Recurrente/Svix; migración a NeoPay pendiente)**

## B. Riesgos o vacíos detectados

1. **Brecha principal de certificación:** el código actual está acoplado a **Recurrente/Svix**, no a NeoPay/NeoNet.
2. **Datos de infraestructura faltantes:** IP pública test/prod, proveedor hosting exacto, topología de red (WAF/CDN/LB/proxy).
3. **TLS sin evidencia operativa en repo:** no se puede afirmar método de cifrado (128/256/etc.) sin inspección externa.
4. **Ambiente de pruebas no identificado claramente:** no aparece URL staging inequívoca en configuración versionada.
5. **OWASP sin artefacto formal:** sí hay prácticas en código, pero no evidencia de auditoría/pentest/certificación.
6. **Riesgo documental:** hay markdowns históricos con valores/escenarios de pruebas de Recurrente que pueden confundir el proceso NeoPay.

## C. Preguntas exactas que debes hacer al proveedor o al admin del servidor

1. ¿Cuál es la **IP pública** del ambiente de **pruebas** y la de **producción**?
2. ¿El hosting de producción es **propio o contratado**? Indicar proveedor (ej. Hostinger, DigitalOcean, AWS, etc.) y titular de la cuenta.
3. ¿Cuál es el **sistema operativo** y versión de cada entorno (test/prod)?
4. ¿Qué **servidor web** usan en producción (Nginx o Apache) y dónde termina TLS (servidor, load balancer o CDN)?
5. ¿Cuál es el **certificado SSL/TLS** activo (emisor, vigencia, renovación, dominios SAN/CN)?
6. ¿Qué **versión TLS** y **cifrados** están habilitados (para responder el campo de método de encriptación)?
7. ¿Existe **WAF / reverse proxy / Cloudflare** delante del servidor? Si sí, ¿cuál es la IP origen real del backend?
8. ¿Cuál es la **URL HTTPS funcional de pruebas** (staging/sandbox) y está accesible para certificación?
9. Para NeoPay/NeoNet: ¿cuáles son los **endpoints API TEST/LIVE**, esquema de autenticación, formato de webhook y firma?
10. Para NeoPay/NeoNet: ¿qué **IPs de origen** o rangos debe permitirse en firewall para webhooks/callbacks?
11. ¿Qué evidencia formal de seguridad tienen (pentest, SAST/DAST, política OWASP/ASVS) para adjuntar en certificación?
12. ¿Quién será el **responsable de soporte** (propio/tercero), horarios y contacto para incidentes de pagos?

---

## Nota de consistencia importante

Desde la evidencia del repositorio, la integración activa sigue siendo **Recurrente** (cliente, rutas y webhook). Si en el formulario ya debes declarar **NeoPay/NeoNet**, conviene responder con transparencia que la migración está en curso y adjuntar plan/fecha de corte para evitar observaciones durante certificación.
