<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\SendInvitationController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Middleware\AdminMiddleware;

/*
|--------------------------------------------------------------------------
| Configuración de prefijo dinámico
|--------------------------------------------------------------------------
| Si estás en producción (Hostinger), las rutas se sirven desde /eventos-app.
| En local, el prefijo será vacío para mantener las rutas limpias.
|--------------------------------------------------------------------------
*/
$prefix = app()->environment('production') ? 'eventos-app' : '';

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
Route::prefix($prefix)->group(function () {

    // Página de inicio → redirige al login
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Invitaciones (sin login)
    Route::get('/invitations/{code}', [RsvpController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{code}/rsvp', [RsvpController::class, 'store'])->name('invitations.rsvp');

    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas (solo administrador)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', AdminMiddleware::class])->group(function () {

        // 📊 Dashboard principal
        Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');

        // CRUD de eventos
        Route::resource('events', EventController::class);

        // CRUD de invitados
        Route::resource('guests', GuestController::class);
        Route::post('/importar-excel', [GuestController::class, 'importarExcel'])->name('importarExcel');

        // CRUD de regalos
        Route::resource('gifts', GiftController::class);
        Route::delete('/gifts/delete-image/{id}', [GiftController::class, 'deleteImage'])->name('gifts.deleteImage');

        // Módulo de envío de invitaciones
        Route::prefix('send-invitations')->group(function () {
            Route::get('/', [SendInvitationController::class, 'index'])->name('sendInvitations.index');
            Route::get('/event/{event_id}/guests', [SendInvitationController::class, 'getGuestsByEvent'])->name('sendInvitations.getGuestsByEvent');
            Route::post('/send', [SendInvitationController::class, 'store'])->name('sendInvitations.store');
        });

        // WhatsApp Web
        Route::post('/send-invitations/whatsapp', [SendInvitationController::class, 'sendAllWhatsApp'])->name('send.whatsapp');
    });

    /*
    |--------------------------------------------------------------------------
    | Autenticación
    |--------------------------------------------------------------------------
    */
    require __DIR__ . '/auth.php';
});
