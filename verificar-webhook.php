<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ESTADO DEL WEBHOOK DE RECURRENTE ===\n\n";

// 1. Estadísticas
$totalIntenciones = App\Models\Intention::count();
$recurrenteIntenciones = App\Models\Intention::where('payment_method', 'recurrente')->count();
$otrasIntenciones = $totalIntenciones - $recurrenteIntenciones;

echo "📊 ESTADÍSTICAS:\n";
echo "   Total de intenciones: {$totalIntenciones}\n";
echo "   De Recurrente: {$recurrenteIntenciones}\n";
echo "   Otros métodos: {$otrasIntenciones}\n\n";

// 2. Intenciones de Recurrente
if ($recurrenteIntenciones > 0) {
    echo "✅ INTENCIONES DE RECURRENTE:\n";
    $intenciones = App\Models\Intention::where('payment_method', 'recurrente')
        ->latest()
        ->get(['id', 'payment_intent_id', 'donor_name', 'email', 'amount', 'status', 'created_at']);
    
    foreach ($intenciones as $i) {
        echo sprintf(
            "   ID: %d | Payment: %s\n   Donante: %s (%s)\n   Monto: Q%.2f | Estado: %s\n   Fecha: %s\n\n",
            $i->id,
            $i->payment_intent_id,
            $i->donor_name,
            $i->email,
            $i->amount,
            $i->status,
            $i->created_at
        );
    }
} else {
    echo "⚠️  NO HAY INTENCIONES DE RECURRENTE\n\n";
}

// 3. Configuración
echo "⚙️  CONFIGURACIÓN:\n";
$svixSecret = config('services.svix.webhook_secret');
$recurrentePublic = config('services.recurrente.public_key');
$recurrenteSecret = config('services.recurrente.secret_key');

echo "   SVIX_WEBHOOK_SECRET: " . substr($svixSecret, 0, 15) . "***\n";
echo "   RECURRENTE_PUBLIC_KEY: " . substr($recurrentePublic, 0, 20) . "***\n";
echo "   RECURRENTE_SECRET_KEY: " . substr($recurrenteSecret, 0, 12) . "***\n";
echo "   APP_URL: " . config('app.url') . "\n";
echo "   Webhook URL: " . config('app.url') . "/api/webhook/recurrente\n\n";

// 4. Logs recientes
echo "📋 ÚLTIMOS LOGS DE WEBHOOK:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = shell_exec("tail -n 500 {$logFile} | grep 'Webhook Recurrente' | tail -n 10");
    if ($logs) {
        echo $logs;
    } else {
        echo "   (No hay logs recientes de webhooks)\n";
    }
} else {
    echo "   (Archivo de logs no encontrado)\n";
}

echo "\n✅ CONCLUSIÓN:\n";
if ($recurrenteIntenciones > 0) {
    echo "   El webhook SÍ está funcionando correctamente.\n";
    echo "   Se han registrado {$recurrenteIntenciones} intención(es) de Recurrente.\n";
} else {
    echo "   El webhook está configurado pero no ha registrado intenciones aún.\n";
    echo "   Realiza un pago de prueba para verificar.\n";
}
