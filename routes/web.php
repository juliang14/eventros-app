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
Route::get('/invitations/{code}', [RsvpController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{code}/rsvp', [RsvpController::class, 'store'])->name('invitations.rsvp');



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
    Route::post('/importar-excel', [GuestController::class, 'importarExcel'])->name('importarExcel');

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
