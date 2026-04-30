# Envío Diario de Backups vía Relay Hostinger

Este documento describe cómo aprovechar tu servidor en Hostinger (que sí puede enviar correos) como relay de correo para la aplicación desplegada en DigitalOcean (DO). El objetivo es que, después de generar el respaldo diario (`backup:database --keep=14`), se adjunte y se envíe por correo usando la infraestructura de Hostinger.

## Arquitectura general

1. **DigitalOcean (DO)**: ejecuta el comando `backup:database`, almacena el `.sql` en `storage/app/backups` y realiza una petición HTTP autenticada hacia Hostinger adjuntando el archivo.
2. **Hostinger**: recibe el archivo, lo valida con una API key compartida, lo reenvía por correo usando su configuración SMTP y opcionalmente guarda una copia en su propio almacenamiento/Cloud para auditoría.
3. **Correo destinatario**: bandeja (p. ej. `respaldos@tu-dominio.com`) que recibirá los .sql comprimidos diariamente.

## Preparación en Hostinger (relay)

1. **Configurar el mailer**
   - En la app Laravel de Hostinger define las variables SMTP reales (`MAIL_MAILER=smtp`, `MAIL_HOST=smtp.hostinger.com`, `MAIL_PORT=465`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION=tls`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`).
   - Verifica SPF/DKIM en el DNS del dominio para evitar rebotes.

2. **Crear credencial para el relay**
   - Genera un UUID y guárdalo como `BACKUP_RELAY_KEY` en el `.env` de Hostinger.
   - Comparte ese valor con la app de DO mediante un canal seguro (1Password, Vault, etc.).

3. **Implementar endpoint de recepción**
   - En `routes/api.php` agrega algo como:
     ```php
     Route::middleware('throttle:backups,10,1')->post('/relay/backups', BackupRelayController::class);
     ```
   - El controlador debe:
     1. Validar el header `X-Relay-Key` contra `BACKUP_RELAY_KEY`.
     2. Validar payload (`file` requerido, `filename`, `ran_at`, `checksum`).
     3. Guardar temporalmente el archivo en `storage/app/relay-backups` (opcional zip).
     4. Despachar un job (`SendBackupEmailJob`) que adjunte el archivo y lo mande a los destinatarios.

4. **Job para enviar el correo**
   - Crea un Mailable `BackupReadyMail` que acepte el archivo y meta-datos.
   - El Job debe usar `Mail::to(config('backup-relay.recipients'))->send(new BackupReadyMail($path, $meta));`
   - Configura los destinatarios en `config/backup-relay.php` (`BACKUP_RELAY_RECIPIENTS="respaldos@tu-dominio.com,devops@tu-dominio.com"`).
   - Habilita un worker de colas en Hostinger (`php artisan queue:work --queue=default,mail --sleep=3 --tries=1`).

5. **Retención y limpieza**
   - Programa un comando en Hostinger para limpiar `storage/app/relay-backups` después de 7 días o subirlos a Object Storage si deseas segunda copia.

### Código en Hostinger (completo)

Coloca los siguientes archivos en la app Laravel hospedada en Hostinger.

#### 1. Variables `.env`

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=cotizaciones@distribuidorajadi.site
MAIL_PASSWORD="Jadi2025."
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=cotizaciones@distribuidorajadi.site
MAIL_FROM_NAME="${APP_NAME}"

BACKUP_RELAY_SECRET="74666688dbbb2aba0afd961a2702aa412480dd882e169c3fd0804029de078b63"
BACKUP_RELAY_RECIPIENTS="respaldos@tu-dominio.com,devops@tu-dominio.com"
```

#### 2. Config `config/services.php`

Agrega al array devuelto:

```php
'backup_relay' => [
   'secret' => env('BACKUP_RELAY_SECRET'),
],
```

#### 3. Config `config/backup-relay.php`

```php
<?php

return [
   'recipients' => array_values(array_filter(array_map('trim', explode(',', (string) env('BACKUP_RELAY_RECIPIENTS', ''))))),
];
```

#### 4. Ruta `routes/api.php`

```php
use App\Http\Controllers\BackupRelayController;

Route::middleware(['throttle:backups,10,1'])->post('/relay/backups', BackupRelayController::class);
```

#### 5. Controlador `app/Http/Controllers/BackupRelayController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Jobs\SendBackupEmailJob;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class BackupRelayController extends Controller
{
   public function __invoke(Request $request)
   {
      $secret = config('services.backup_relay.secret');
      if (!$secret || $request->header('X-Relay-Key') !== $secret) {
         return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
      }

      /** @var UploadedFile $file */
      $data = $request->validate([
         'file' => ['required', 'file', 'max:51200'],
         'filename' => ['required', 'string', 'max:255'],
         'original_filename' => ['nullable', 'string', 'max:255'],
         'checksum' => ['required', 'string'],
         'ran_at' => ['required', 'date'],
         'app_environment' => ['required', 'string', 'max:50'],
         'recipients' => ['nullable', 'string'],
      ]);

      $storedPath = $data['file']->storeAs(
         'relay-backups/'.now()->format('Y-m-d'),
         Str::random(8).'_'.$data['filename']
      );

      SendBackupEmailJob::dispatch(
         storage_path('app/'.$storedPath),
         $data['filename'],
         $data
      );

      return response()->json([
         'ok' => true,
         'stored_path' => $storedPath,
      ]);
   }
}
```

#### 6. Job `app/Jobs/SendBackupEmailJob.php`

```php
<?php

namespace App\Jobs;

use App\Mail\BackupReadyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class SendBackupEmailJob implements ShouldQueue
{
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public function __construct(
      protected string $path,
      protected string $displayName,
      protected array $meta
   ) {
   }

   public function handle(): void
   {
      $recipients = $this->resolveRecipients();
      if (empty($recipients)) {
         return;
      }

      Mail::to($recipients)
         ->send(new BackupReadyMail($this->path, $this->displayName, $this->meta));
   }

   protected function resolveRecipients(): array
   {
      if (!empty($this->meta['recipients'])) {
         return array_values(array_filter(array_map('trim', explode(',', $this->meta['recipients']))));
      }

      return config('backup-relay.recipients', []);
   }
}
```

#### 7. Mailable `app/Mail/BackupReadyMail.php`

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BackupReadyMail extends Mailable implements ShouldQueue
{
   use Queueable, SerializesModels;

   public function __construct(
      protected string $path,
      protected string $displayName,
      protected array $meta
   ) {
   }

   public function build(): self
   {
      $subject = 'Backup '.$this->meta['ran_at'].' ('.$this->meta['app_environment'].')';

      return $this->subject($subject)
         ->markdown('emails.backups.ready', [
            'meta' => $this->meta,
         ])
         ->attach($this->path, [
            'as' => $this->displayName,
            'mime' => 'application/octet-stream',
         ]);
   }
}
```

#### 8. Vista `resources/views/emails/backups/ready.blade.php`

```blade
@component('mail::message')
## Backup recibido

- Archivo: **{{ $meta['filename'] ?? 'N/D' }}**
- Generado: {{ $meta['ran_at'] ?? 'N/D' }}
- Entorno: {{ $meta['app_environment'] ?? 'N/D' }}
- Checksum SHA256: `{{ $meta['checksum'] ?? 'N/D' }}`

Gracias.
@endcomponent
```

#### 9. Worker de colas

```bash
php artisan queue:work --queue=default,mail --sleep=3 --tries=1
```

Mantén este proceso corriendo con Supervisor o el gestor de procesos de Hostinger.

## Preparación en DigitalOcean

1. **Variables de entorno**
    - Añade en `.env`:
       ```ini
       BACKUP_RELAY_URL=https://tu-dominio-hostinger.com/api/relay/backups
       BACKUP_RELAY_KEY=uuid-compartido
       BACKUP_RELAY_RECIPIENTS="respaldos@tu-dominio.com"
       BACKUP_RELAY_COMPRESS=true
       BACKUP_RELAY_TIMEOUT=60
       ```
    - Mantén `backup:database` generando archivos en `storage/app/backups`.

2. **Comando para enviar el respaldo**
   - Usa el nuevo comando `php artisan backup:mail` que localiza el último archivo en `storage/app/backups` (o acepta `--path=/ruta/a/backup.sql`).
   - Internamente despacha el job `App\Jobs\SendBackupToRelay` que:
     1. Comprime el archivo (`.gz`) si `BACKUP_RELAY_COMPRESS=true`.
     2. Calcula el checksum `sha256_file($path)`.
     3. Envía un POST `multipart/form-data` a `BACKUP_RELAY_URL` con campos `filename`, `original_filename`, `ran_at`, `checksum`, `app_environment`, `recipients`.
     4. Incluye el header `X-Relay-Key: BACKUP_RELAY_KEY` y un timeout configurable (`BACKUP_RELAY_TIMEOUT`).
     5. Registra éxito o fallo en los logs de Laravel para observabilidad.

3. **Scheduler**
   - En `routes/schedule.php` (ya compartido con el Kernel) agrega, después del comando actual:
     ```php
     $schedule->command('backup:mail')->timezone('America/Guatemala')->dailyAt('19:10');
     ```
   - Con esto, el backup se genera a las 19:00 y se envía 10 minutos después para asegurar que el archivo exista.

4. **Monitoreo y reintentos**
   - Configura el comando/job para reintentar hasta 3 veces en caso de HTTP 5xx o timeouts y para fallas definitivas notificar por Slack o Telegram.
   - Habilita logs dedicados (`storage/logs/backup-relay.log`).

## Pruebas de punta a punta

1. Ejecuta manualmente en DO:
   ```bash
   php artisan backup:database --keep=14
   php artisan backup:mail --env=production
   ```
   Debes recibir un `201 Created` del endpoint y ver el archivo en la bandeja.

2. Verifica en Hostinger:
   - Logs de la cola (`php artisan queue:failed`) vacíos.
   - Correo recibido con el adjunto correcto.
   - Archivo temporal almacenado y con permisos 0640.

3. Ajusta el cron/supervisor en DO para correr `artisan schedule:run` cada minuto si aún no lo hace (`* * * * * cd /var/www/santo-domingo && php artisan schedule:run >> /dev/null 2>&1`).

## Seguridad

- Usa HTTPS obligatorio para el endpoint.
- Rota `BACKUP_RELAY_KEY` al menos cada 90 días y mantenlo fuera de repos públicos.
- Limita el tamaño máximo de archivo en Hostinger (`max:51200` ≈ 50 MB) y utiliza `Request::ip()` para permitir solo la IP pública del droplet si es posible.
- Considera cifrar el backup antes de enviarlo (`openssl enc -aes-256-cbc`).

Con estos pasos el flujo de backups diarios quedará automatizado y auditado, usando Hostinger como relay confiable para el envío de correos con adjuntos desde tu servidor de DigitalOcean.
