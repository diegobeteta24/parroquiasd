<?php

use Svix\Webhook;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$secret = config('services.svix.webhook_secret') ?? env('SVIX_WEBHOOK_SECRET');
if (!$secret) {
    fwrite(STDERR, "Missing SVIX_WEBHOOK_SECRET env var\n");
    exit(1);
}

$payload = json_encode([
    'id' => 'pi_test_manual_' . time(),
    'event_type' => 'payment_intent.succeeded',
    'amount_in_cents' => 5000,
    'currency' => 'GTQ',
    'created_at' => Carbon::now('UTC')->toIso8601String(),
    'metadata' => [
        'mass_id' => 365,
        'intention_type' => 'normal',
        'category' => 'accion_de_gracias',
        'public_text' => 'Webhook manual',
        'donor_name' => 'Test Svix Manual',
        'donor_email' => 'test@example.com',
        'donor_phone' => null,
    ],
], JSON_THROW_ON_ERROR);

$headers = [
    'svix-id' => 'msg_' . bin2hex(random_bytes(8)),
    'svix-timestamp' => (string) time(),
];

$webhook = new Webhook($secret);
$headers['svix-signature'] = $webhook->sign($headers['svix-id'], $headers['svix-timestamp'], $payload);

$ch = curl_init('https://basilicadelrosario.gt/api/webhook/recurrente');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'svix-id: ' . $headers['svix-id'],
        'svix-timestamp: ' . $headers['svix-timestamp'],
        'svix-signature: ' . $headers['svix-signature'],
    ],
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    fwrite(STDERR, "Curl error: {$error}\n");
    exit(1);
}

echo "Status: {$status}\n";
echo "Response: {$response}\n";
