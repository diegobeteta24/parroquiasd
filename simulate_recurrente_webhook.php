#!/usr/bin/env php
<?php

/**
 * Simulador de Webhook de Recurrente/Svix
 * 
 * Este script simula un webhook real de Svix hacia el endpoint de Laravel.
 * Genera la firma HMAC-SHA256 correcta para que pase la validación.
 * 
 * Uso: php simulate_recurrente_webhook.php
 */

require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Colors for terminal output
const COLOR_GREEN = "\033[32m";
const COLOR_RED = "\033[31m";
const COLOR_YELLOW = "\033[33m";
const COLOR_BLUE = "\033[34m";
const COLOR_RESET = "\033[0m";

echo COLOR_BLUE . "=== Simulador de Webhook Recurrente/Svix ===" . COLOR_RESET . PHP_EOL . PHP_EOL;

// 1) Leer el secreto de Svix desde variable de entorno
$secret = getenv('SVIX_WEBHOOK_SECRET') ?: ($_ENV['SVIX_WEBHOOK_SECRET'] ?? null);

if (empty($secret)) {
    fwrite(STDERR, COLOR_RED . "ERROR: La variable de entorno SVIX_WEBHOOK_SECRET no está configurada." . COLOR_RESET . PHP_EOL);
    fwrite(STDERR, "Por favor, configúrala en tu archivo .env y recarga la configuración." . PHP_EOL);
    exit(1);
}

echo COLOR_GREEN . "✓" . COLOR_RESET . " Secreto de Svix encontrado: " . substr($secret, 0, 15) . "..." . PHP_EOL;

// 2) Construir payload JSON de prueba
$timestamp = time();
$payload = [
    'id' => 'pi_test_manual_' . $timestamp,
    'event_type' => 'payment_intent.succeeded',
    'amount_in_cents' => 5000,
    'currency' => 'GTQ',
    'created_at' => date('c'), // ISO 8601
    'metadata' => [
        'mass_id' => null,
        'intention_type' => 'normal',
        'category' => 'acciones_de_gracia',
        'public_text' => 'Prueba manual desde script - ' . date('Y-m-d H:i:s'),
        'donor_name' => 'Test Manual Svix',
        'donor_email' => 'test@example.com',
        'donor_phone' => null,
    ],
];

$body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

echo COLOR_GREEN . "✓" . COLOR_RESET . " Payload generado:" . PHP_EOL;
echo COLOR_YELLOW . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . COLOR_RESET . PHP_EOL . PHP_EOL;

// 3) Calcular la firma de Svix
echo "Generando firma HMAC-SHA256..." . PHP_EOL;

// Generar ID y timestamp del webhook
$webhookId = 'msg_' . bin2hex(random_bytes(8));
$webhookTimestamp = (string) $timestamp;

echo "  webhook-id: " . $webhookId . PHP_EOL;
echo "  webhook-timestamp: " . $webhookTimestamp . PHP_EOL;

// Construir cadena a firmar
$signedContent = $webhookId . '.' . $webhookTimestamp . '.' . $body;

// Extraer la clave de firma (quitar prefijo "whsec_" y decodificar base64)
if (substr($secret, 0, 6) !== 'whsec_') {
    fwrite(STDERR, COLOR_RED . "ERROR: El secreto de Svix debe comenzar con 'whsec_'" . COLOR_RESET . PHP_EOL);
    exit(1);
}

// Quitar prefijo "whsec_"
$secretPart = substr($secret, 6);

// Decodificar en base64 para obtener la clave real
$key = base64_decode($secretPart);

if ($key === false) {
    fwrite(STDERR, COLOR_RED . "ERROR: No se pudo decodificar el secreto de Svix" . COLOR_RESET . PHP_EOL);
    exit(1);
}

// Calcular HMAC SHA256 usando la clave decodificada
$rawSig = hash_hmac('sha256', $signedContent, $key, true);
$base64Sig = base64_encode($rawSig);

// Armar header de firma en formato Svix
$signatureHeader = 'v1,' . $base64Sig;

echo "  webhook-signature: v1," . substr($base64Sig, 0, 20) . "..." . PHP_EOL;
echo COLOR_GREEN . "✓" . COLOR_RESET . " Firma generada correctamente" . PHP_EOL . PHP_EOL;

// 4) Hacer petición HTTP POST con cURL
$url = 'https://basilicadelrosario.gt/api/webhook/recurrente';

echo "Enviando webhook a: " . COLOR_BLUE . $url . COLOR_RESET . PHP_EOL;

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'svix-id: ' . $webhookId,
        'svix-timestamp: ' . $webhookTimestamp,
        'svix-signature: ' . $signatureHeader,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
]);

// 5) Ejecutar petición y obtener respuesta
echo "Ejecutando petición..." . PHP_EOL . PHP_EOL;

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// Imprimir resultado
echo str_repeat('=', 60) . PHP_EOL;
echo "RESULTADO" . PHP_EOL;
echo str_repeat('=', 60) . PHP_EOL . PHP_EOL;

if ($curlError) {
    echo COLOR_RED . "cURL error: " . $curlError . COLOR_RESET . PHP_EOL;
    exit(1);
}

// Colorear código de respuesta según sea éxito o error
if ($httpCode >= 200 && $httpCode < 300) {
    echo COLOR_GREEN . "HTTP " . $httpCode . COLOR_RESET . PHP_EOL;
} elseif ($httpCode >= 400) {
    echo COLOR_RED . "HTTP " . $httpCode . COLOR_RESET . PHP_EOL;
} else {
    echo COLOR_YELLOW . "HTTP " . $httpCode . COLOR_RESET . PHP_EOL;
}

echo PHP_EOL . "Response body:" . PHP_EOL;
echo str_repeat('-', 60) . PHP_EOL;

// Intentar formatear JSON si es posible
$jsonResponse = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($jsonResponse)) {
    echo json_encode($jsonResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} else {
    echo $response . PHP_EOL;
}

echo str_repeat('-', 60) . PHP_EOL . PHP_EOL;

// Verificación final
if ($httpCode === 200) {
    echo COLOR_GREEN . "✓ Webhook procesado exitosamente" . COLOR_RESET . PHP_EOL;
    echo PHP_EOL . "Puedes verificar en la base de datos:" . PHP_EOL;
    echo COLOR_YELLOW . "  php artisan tinker --execute=\"App\\Models\\Intention::where('payment_intent_id', '{$payload['id']}')->first()\"" . COLOR_RESET . PHP_EOL;
    exit(0);
} elseif ($httpCode === 400) {
    echo COLOR_RED . "✗ El webhook fue rechazado (firma inválida o datos incorrectos)" . COLOR_RESET . PHP_EOL;
    exit(1);
} else {
    echo COLOR_YELLOW . "⚠ Código de respuesta inesperado" . COLOR_RESET . PHP_EOL;
    exit(1);
}
