<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Admin\Dashboard as AdminDashboard;
use App\Http\Controllers\Admin\MassInstanceController;
use App\Http\Controllers\Admin\PriestController;
use App\Http\Controllers\Admin\ReportsController;

Route::middleware(['auth','verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        // Dashboard
        Route::get('/', AdminDashboard::class)->name('dashboard');

        // Debug: whoami (temporary helper; keep behind auth)
        Route::get('/whoami', function () {
            $user = auth()->user();
            return response()->json([
                'email' => $user?->email,
                'id' => $user?->id,
                'verified' => (bool) ($user?->email_verified_at),
                'roles' => $user?->getRoleNames() ?? [],
                'guard' => config('auth.defaults.guard'),
                'app_url' => config('app.url'),
                'session_domain' => config('session.domain'),
            ]);
        })->name('whoami');

        // Misa ordinaria: vista para imprimir hoja
        Route::get('/masses/{mass}/print', [MassInstanceController::class,'print'])->name('masses.print');

        // Special masses CRUD (controller will ensure is_special true)
        Route::resource('special-masses', \App\Http\Controllers\Admin\SpecialMassController::class);

        // Priests: lightweight JSON search used by Alpine in the assign modal
        Route::get('/priests/search', [PriestController::class, 'search'])->name('priests.search');

        // Reports
        Route::get('/reports/income', [ReportsController::class, 'income'])->name('reports.income');
    });
