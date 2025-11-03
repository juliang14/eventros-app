<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use App\Models\SendInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SendInvitationController extends Controller
{
    /**
     * Mostrar vista principal (lista de eventos / UI).
     */
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->get();
        return view('sendInvitations.index', compact('events'));
    }

    /**
     * Retorna los invitados de un evento (JSON) — pensado para fetch/ajax.
     * Ruta esperada: GET /send-invitations/event/{event_id}/guests
     */
    public function getGuestsByEvent($event_id)
    {
        $event = Event::findOrFail($event_id);

        // Traer invitados del evento con su rsvp y el registro de envío (si existe)
        $guests = Guest::where('event_id', $event->id)
            ->with(['rsvp'])
            ->get()
            ->map(function ($g) use ($event) {
                // buscar un SendInvitation existente (si existe)
                $inv = SendInvitation::where('event_id', $g->event_id)
                    ->where('guest_id', $g->id)
                    ->first();

                return [
                    'guest_id'   => $g->id,
                    'guest_name' => $g->name,
                    'email'      => $g->email,
                    'phone'      => $g->phone,
                    'invite_code'=> $g->invite_code,
                    'rsvp'       => $g->rsvp ? $g->rsvp->status : null,
                    'invitation' => $inv ? [
                        'id'      => $inv->id,
                        'channel' => $inv->channel,
                        'status'  => $inv->status,
                        'message' => $inv->message,
                        'updated_at' => $inv->updated_at ? $inv->updated_at->toDateTimeString() : null,
                    ] : null,
                ];
            });

        return response()->json($guests);
    }

    /**
     * Enviar invitaciones.
     * Acepta:
     * - event_id (required)
     * - channel (email|whatsapp|both)
     * - guest_ids (optional) array -> lista específica de invitados
     * - resend_failed (optional boolean) -> reenviar sólo fallidas
     *
     * Ruta esperada: POST /send-invitations/send
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id'     => 'required|exists:ev_events,id',
            'channel'      => 'required|in:email,whatsapp,both',
            'guest_ids'    => 'sometimes|array',
            'guest_ids.*'  => 'integer|exists:ev_guests,id',
            'resend_failed'=> 'sometimes|boolean',
        ]);

        $event = Event::findOrFail($data['event_id']);
        $channel = $data['channel'];

        // Query base de invitados del evento
        $guestsQuery = Guest::where('event_id', $event->id);

        // Si se envía lista puntual
        if (!empty($data['guest_ids'])) {
            $guestsQuery->whereIn('id', $data['guest_ids']);
        }

        // Si se pide reenviar fallidas, ajustamos la query con ids de SendInvitation que fallaron
        if (!empty($data['resend_failed'])) {
            $failedGuestIds = SendInvitation::where('event_id', $event->id)
                ->where('status', SendInvitation::STATUS_FAILED)
                ->pluck('guest_id')
                ->toArray();

            // si no hay fallidas, devolver info inmediatamente
            if (empty($failedGuestIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No hay invitaciones fallidas para reenviar.',
                    'sent' => 0,
                    'failed' => 0,
                ]);
            }

            $guestsQuery->whereIn('id', $failedGuestIds);
        }

        $guests = $guestsQuery->get();

        $sentCount = 0;
        $failedCount = 0;
        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($guests as $guest) {
                // Mensaje base personalizable
                $baseMessage = "Hola {$guest->name},\nEstás invitado al evento \"{$event->title}\" el día {$event->event_date->format('d/m/Y H:i')}.\nVerifica tu invitación: " . url('/invitations/'.$guest->invite_code);

                // Registrar / actualizar SendInvitation (status pending por ahora)
                $invitation = SendInvitation::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'guest_id' => $guest->id,
                    ],
                    [
                        'channel' => $channel,
                        'status'  => SendInvitation::STATUS_PENDING,
                        'message' => $baseMessage,
                    ]
                );

                $finalMessage = $baseMessage;
                $overallStatus = SendInvitation::STATUS_SENT; // asumimos éxito, si algo falla lo marcamos

                // Email
                if ($channel === SendInvitation::CHANNEL_EMAIL || $channel === SendInvitation::CHANNEL_BOTH) {
                    if (!empty($guest->email)) {
                        try {
                            Mail::raw($finalMessage, function ($m) use ($guest, $event) {
                                $m->to($guest->email)
                                  ->subject("Invitación: {$event->title}");
                            });
                        } catch (Throwable $e) {
                            // marca fallo
                            $overallStatus = SendInvitation::STATUS_FAILED;
                            $finalMessage .= "\n\nError Email: " . $e->getMessage();
                        }
                    } else {
                        $overallStatus = SendInvitation::STATUS_FAILED;
                        $finalMessage .= "\n\nError: invitado no tiene email registrado.";
                    }
                }

                // WhatsApp (solo anexamos link; envío manual desde la UI si quieres)
                if ($channel === SendInvitation::CHANNEL_WHATSAPP || $channel === SendInvitation::CHANNEL_BOTH) {
                    if (!empty($guest->phone)) {
                        $waLink = "https://wa.me/{$guest->phone}?text=" . rawurlencode($finalMessage);
                        $finalMessage .= "\n\nWhatsApp link: {$waLink}";
                    } else {
                        $overallStatus = SendInvitation::STATUS_FAILED;
                        $finalMessage .= "\n\nError: invitado no tiene teléfono registrado.";
                    }
                }

                // Guardar resultado
                $invitation->message = $finalMessage;
                $invitation->status = $overallStatus;
                $invitation->channel = $channel;
                $invitation->save();

                if ($overallStatus === SendInvitation::STATUS_SENT) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }

                $createdCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Proceso finalizado",
                'sent' => $sentCount,
                'failed' => $failedCount,
                'processed' => $createdCount,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Error procesando invitaciones: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enviar todas las invitaciones por WhatsApp
     */
    public function sendAllWhatsApp(Request $request)
    {
        $eventId = $request->input('event_id');
        $event = Event::findOrFail($eventId);

        // 🔹 Traer invitados con número válido
        $guests = Guest::where('event_id', $eventId)
            ->whereNotNull('phone')
            ->get();

        if ($guests->isEmpty()) {
            return response()->json(['error' => 'No hay invitados con número de teléfono.'], 400);
        }

        $sent = 0;
        $failed = 0;
        $baseUrl = 'http://localhost:3000/send';

        foreach ($guests as $guest) {
            try {
                // Personalizar mensaje
                $msg = str_replace(
                    ['XXXNOMBRESXXX', 'XXXCOMPANIONSXXX'],
                    [$guest->name, $guest->companions_names ?? ''],
                    $event->description
                );

                // Enviar a Node.js
                $response = Http::post($baseUrl, [
                    'phone' => $guest->phone,
                    'message' => $msg
                ]);

                $status = $response->ok() ? 'sent' : 'failed';
                if ($status === 'sent') $sent++; else $failed++;

                // Guardar registro o actualizar
                SendInvitation::updateOrCreate(
                    ['event_id' => $eventId, 'guest_id' => $guest->id],
                    [
                        'channel' => 'whatsapp',
                        'status' => $status,
                        'message' => $msg
                    ]
                );

            } catch (\Throwable $th) {
                $failed++;
                SendInvitation::updateOrCreate(
                    ['event_id' => $eventId, 'guest_id' => $guest->id],
                    [
                        'channel' => 'whatsapp',
                        'status' => 'failed',
                        'message' => $th->getMessage()
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed
        ]);
    }
}
