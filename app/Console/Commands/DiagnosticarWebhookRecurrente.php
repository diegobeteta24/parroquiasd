<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnosticarWebhookRecurrente extends Command
{
    protected $signature = 'webhook:diagnosticar';
    protected $description = 'Diagnostica la configuración del webhook de Recurrente';

    public function handle()
    {
        $this->info('🔍 Diagnóstico del Webhook de Recurrente');
        $this->newLine();

        // 1. Verificar variables de entorno
        $this->info('1️⃣ Variables de Entorno:');
        $this->checkEnvVar('SVIX_WEBHOOK_SECRET');
        $this->checkEnvVar('RECURRENTE_PUBLIC_KEY');
        $this->checkEnvVar('RECURRENTE_SECRET_KEY');
        $this->checkEnvVar('APP_URL');
        $this->newLine();

        // 2. Verificar configuración
        $this->info('2️⃣ Configuración de servicios:');
        $svixSecret = config('services.svix.webhook_secret');
        $recurrentePublic = config('services.recurrente.public_key');
        $recurrenteSecret = config('services.recurrente.secret_key');
        
        $this->displayConfig('services.svix.webhook_secret', $svixSecret);
        $this->displayConfig('services.recurrente.public_key', $recurrentePublic);
        $this->displayConfig('services.recurrente.secret_key', $recurrenteSecret);
        $this->newLine();

        // 3. Verificar ruta del webhook
        $this->info('3️⃣ Ruta del Webhook:');
        $webhookUrl = config('app.url') . '/api/webhook/recurrente';
        $this->line("   URL: <comment>{$webhookUrl}</comment>");
        $this->newLine();

        // 4. Verificar últimos logs
        $this->info('4️⃣ Últimos webhooks recibidos:');
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            $logs = shell_exec("tail -n 100 {$logFile} | grep 'Webhook Recurrente' | tail -n 5");
            if ($logs) {
                $this->line($logs);
            } else {
                $this->warn('   No se encontraron logs de webhooks recientes');
            }
        } else {
            $this->error('   Archivo de logs no encontrado');
        }
        $this->newLine();

        // 5. Instrucciones
        $this->info('📝 Pasos para solucionar:');
        $this->line('   1. Ve a https://app.svix.com');
        $this->line('   2. Busca tu endpoint: ' . $webhookUrl);
        $this->line('   3. Copia el "Webhook Secret" (empieza con whsec_)');
        $this->line('   4. Actualiza SVIX_WEBHOOK_SECRET en tu archivo .env');
        $this->line('   5. Ejecuta: php artisan config:clear');
        $this->line('   6. Prueba enviando un webhook de TEST desde Svix');
        $this->newLine();

        // 6. Test de endpoint
        $this->info('🌐 Verificar conectividad:');
        $this->line("   Puedes probar manualmente con:");
        $this->line("   <comment>curl -X POST {$webhookUrl} -H 'Content-Type: application/json' -d '{}'</comment>");
        $this->newLine();

        return 0;
    }

    private function checkEnvVar($key)
    {
        $value = env($key);
        if ($value) {
            $display = $this->maskSecret($key, $value);
            $this->line("   ✅ {$key}: <comment>{$display}</comment>");
        } else {
            $this->line("   ❌ {$key}: <error>NO CONFIGURADO</error>");
        }
    }

    private function displayConfig($key, $value)
    {
        if ($value) {
            $display = $this->maskSecret($key, $value);
            $this->line("   ✅ {$key}: <comment>{$display}</comment>");
        } else {
            $this->line("   ❌ {$key}: <error>NO CONFIGURADO</error>");
        }
    }

    private function maskSecret($key, $value)
    {
        // Solo mostrar primeros caracteres de secretos
        if (str_contains($key, 'SECRET') || str_contains($key, 'secret')) {
            return substr($value, 0, 15) . '***';
        }
        if (str_contains($key, 'sk_')) {
            return substr($value, 0, 12) . '***';
        }
        return $value;
    }
}
