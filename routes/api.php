<?php

use App\Http\Controllers\Webhook\RecurrenteWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook de Recurrente (sin CSRF, verificado por Svix)
Route::post('/webhook/recurrente', [RecurrenteWebhookController::class, 'handle'])
    ->name('webhook.recurrente')
    ->withoutMiddleware(['web']);
