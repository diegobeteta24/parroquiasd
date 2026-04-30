<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Admin\MassCalendarController;
use App\Http\Controllers\PublicIntentionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Certificates\IntentionCertificateController;
use App\Http\Controllers\Certificates\PublicIntentionCertificateController;

// Sitio público
Route::view('/', 'pages.home')->name('home');
Route::view('/historia', 'pages.historia')->name('historia');
Route::view('/galeria', 'pages.galeria')->name('galeria');
Route::view('/horarios', 'pages.horarios')->name('horarios');

// Contacto con formulario funcional
Route::get('/contacto', [ContactController::class, 'show'])->name('contacto');
Route::post('/contacto', [ContactController::class, 'send'])->name('contacto.send');

// Intenciones públicas (Recurrente)
Route::get('/intenciones', [PublicIntentionController::class, 'form'])->name('intentions.form');
Route::post('/intenciones/checkout', [PublicIntentionController::class, 'checkout'])->name('intentions.checkout');
Route::get('/intenciones/success', [PublicIntentionController::class, 'success'])->name('intentions.success');
Route::get('/intenciones/cancel', [PublicIntentionController::class, 'cancel'])->name('intentions.cancel');
Route::get('/intenciones/certificado', IntentionCertificateController::class)->name('intentions.certificate.download');
Route::get('/intenciones/{intention}/certificado/{token}', PublicIntentionCertificateController::class)
    ->name('intentions.certificate.public');

// Ruta de prueba PDF (solo en local)
if (app()->environment('local')) {
    Route::get('/_debug/pdf', function () {
    $html = view('pages.home')->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('test.pdf');
    });
}

// Bloquear registro público explícitamente (404)
Route::any('/register', function () {
    abort(404);
});

Route::middleware(['auth', config('jetstream.auth_session'), 'verified'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        // Calendario visible para Secretaria y Padre
    Route::view('/admin/mass-calendar', 'admin.mass-calendar')->middleware('role:Secretaria|Padre|superadmin')->name('admin.mass-calendar');

        // Reportes solo para Secretaria
    Route::view('/admin/reports', 'admin.reports')->middleware('role:Secretaria|superadmin')->name('admin.reports');
    });

// Admin internal API (protected)
Route::middleware(['auth','verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function(){
        // Eventos del calendario necesarios para ambas, pero edición no
        Route::get('/api/mass-events', [MassCalendarController::class,'index'])
            ->middleware('role:Secretaria|Padre|superadmin')
            ->name('mass-events');

        // Ver detalle de misa solo Secretaria (Padre verá la hoja PDF, no el detalle CRUD)
        Route::get('/mass-instances/{mass}', [\App\Http\Controllers\Admin\MassInstanceController::class,'show'])
            ->middleware('role:Secretaria|superadmin')
            ->name('masses.show');

        // Descargar hoja de intenciones PDF (permitido para Padre y Secretaria)
        Route::get('/mass-instances/{mass}/pdf', [\App\Http\Controllers\Admin\MassInstanceController::class,'pdf'])
            ->middleware('role:Secretaria|Padre|superadmin')
            ->name('masses.pdf');

        // Intentions CRUD: solo Secretaria
        Route::middleware('role:Secretaria|superadmin')->group(function () {
            Route::get('/mass-instances/{mass}/intentions/create', [\App\Http\Controllers\Admin\IntentionController::class,'create'])->name('intentions.create');
            Route::post('/mass-instances/{mass}/intentions', [\App\Http\Controllers\Admin\IntentionController::class,'store'])->name('intentions.store');
            Route::get('/intentions/{intention}/edit', [\App\Http\Controllers\Admin\IntentionController::class,'edit'])->name('intentions.edit');
            Route::put('/intentions/{intention}', [\App\Http\Controllers\Admin\IntentionController::class,'update'])->name('intentions.update');
            Route::delete('/intentions/{intention}', [\App\Http\Controllers\Admin\IntentionController::class,'destroy'])->name('intentions.destroy');
        });

        // Certificado: solo Secretaria
        Route::get('/intentions/{intention}/certificate', [\App\Http\Controllers\Admin\IntentionController::class,'certificate'])
            ->middleware('role:Secretaria|superadmin')
            ->name('intentions.certificate');

        // Gestión de usuarios: SOLO superadmin
        Route::middleware(['role:superadmin'])->group(function(){
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        });
    });

// Reports: Monthly intentions PDF (protected) y listado
Route::middleware(['auth','verified','role:Secretaria|superadmin'])->group(function () {
    Route::get('/reports/monthly', [ReportsController::class, 'monthly'])->name('reports.monthly');
    Route::get('/admin/reports/intentions', [\App\Http\Controllers\Admin\ReportsController::class, 'intentionsList'])->name('admin.reports.intentions');
});


// Ruta temporal de diagnóstico Livewire (eliminar en producción)
if (app()->environment('local')) {
    Route::get('/_livewire-test', \App\Livewire\Demo\TestComponent::class);
}
