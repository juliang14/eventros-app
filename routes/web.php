<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\RsvpController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Página de bienvenida → redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Invitaciones (acceso sin login)
Route::get('/invitacion/{code}', [RsvpController::class, 'show'])->name('invitacion.show');
Route::post('/invitacion/{code}/rsvp', [RsvpController::class, 'rsvp'])->name('invitacion.rsvp');


/*
|--------------------------------------------------------------------------
| Rutas Protegidas (solo administrador)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', AdminMiddleware::class])->group(function () {

    // 📊 Dashboard principal del administrador
    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | CRUD de eventos
    |--------------------------------------------------------------------------
    */
    Route::resource('events', EventController::class);

    /*
    |--------------------------------------------------------------------------
    | CRUD de invitados
    |--------------------------------------------------------------------------
    */
    Route::resource('guests', GuestController::class);

    // 📂 Importar invitados desde archivo Excel
    Route::post('/guests/import', [GuestController::class, 'import'])->name('guests.import');

    /*
    |--------------------------------------------------------------------------
    | CRUD de regalos
    |--------------------------------------------------------------------------
    */
    Route::resource('gifts', GiftController::class);
});

/*
|--------------------------------------------------------------------------
| Autenticación (login, registro, logout)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
