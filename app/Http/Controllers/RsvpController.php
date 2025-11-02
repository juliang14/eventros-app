<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\Gift;
use App\Models\Rsvp;
use Illuminate\Support\Facades\DB;

class RsvpController extends Controller
{
    public function show($invite_code, Request $request)
    {
        $guest = Guest::where('invite_code', $invite_code)->firstOrFail();
        $event = $guest->event;
        $gifts = Gift::where('event_id', $event->id)->get();

        if ($request->has('change')) {
            session(['allow_change' => true]);
        } else {
            session()->forget('allow_change');
        }

        return view('invitations.show', compact('guest', 'event', 'gifts'));
    }

    public function store(Request $request, $invite_code)
    {
        $guest = Guest::where('invite_code', $invite_code)->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:confirmed,declined',
            'gifts' => 'array',
            'gifts.*' => 'integer|exists:ev_gift,id'
        ]);

        DB::transaction(function() use ($guest, $data) {

            // 1️⃣ Crear o actualizar RSVP
            Rsvp::updateOrCreate(
                ['guest_id' => $guest->id],
                ['status' => $data['status']]
            );

            // 2️⃣ Si confirma asistencia, actualizar regalos seleccionados
            if ($data['status'] === 'confirmed' && !empty($data['gifts'])) {
                foreach ($data['gifts'] as $giftId) {
                    $gift = Gift::lockForUpdate()->find($giftId); // Evita conflictos concurrentes

                    if ($gift && $gift->reserved_count < $gift->quantity) {
                        // Revisar si el invitado ya está registrado en reserved_by
                        $reservedBy = $gift->reserved_by ? explode('|', $gift->reserved_by) : [];

                        if (!in_array($guest->id, $reservedBy)) {
                            $reservedBy[] = $guest->id;

                            // Incrementar contador y actualizar reserved_by
                            $gift->update([
                                'reserved_count' => $gift->reserved_count + 1,
                                'reserved_by' => implode('|', $reservedBy)
                            ]);
                        }
                    }
                }
            }

            // 3️⃣ Actualizar campo confirmed del invitado
            $guest->update(['confirmed' => $data['status'] === 'confirmed']);
        });

        return redirect()->back()->with('success', 'Respuesta registrada. ¡Gracias por confirmar tu asistencia!');
    }
}
