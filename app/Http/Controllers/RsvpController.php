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
            'gifts.*' => 'integer|exists:ev_gifts,id'
        ]);

        DB::transaction(function() use ($guest, $data) {

            // 1️⃣ Crear o actualizar RSVP
            Rsvp::updateOrCreate(
                ['guest_id' => $guest->id],
                ['status' => $data['status']]
            );

            // Obtener todos los regalos del evento
            $allGifts = Gift::where('event_id', $guest->event_id)->lockForUpdate()->get();

            foreach ($allGifts as $gift) {
                $reservedBy = $gift->reserved_by ? explode('|', $gift->reserved_by) : [];

                // Si este invitado lo tenía reservado y ya no está en la nueva selección -> liberar
                if (in_array($guest->id, $reservedBy) && (
                    $data['status'] === 'declined' ||
                    empty($data['gifts']) ||
                    !in_array($gift->id, $data['gifts'])
                )) {
                    $reservedBy = array_diff($reservedBy, [$guest->id]);
                    $gift->update([
                        'reserved_count' => max(0, $gift->reserved_count - 1),
                        'reserved_by' => implode('|', $reservedBy)
                    ]);
                }

                // Si confirmó asistencia y seleccionó este regalo -> reservar
                if ($data['status'] === 'confirmed' && !empty($data['gifts']) && in_array($gift->id, $data['gifts'])) {
                    if (!in_array($guest->id, $reservedBy) && $gift->reserved_count < $gift->quantity) {
                        $reservedBy[] = $guest->id;
                        $gift->update([
                            'reserved_count' => $gift->reserved_count + 1,
                            'reserved_by' => implode('|', $reservedBy)
                        ]);
                    }
                }
            }

            // 3️⃣ Actualizar campo confirmed del invitado (1 o 2)
            $guest->update(['confirmed' => $data['status'] === 'confirmed' ? 1 : 2]);
        });

        return redirect()->back()->with('success', 'Tu respuesta fue registrada correctamente 💙');
    }

}
