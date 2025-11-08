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
use Illuminate\Support\Facades\Http;

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

        $guestsQuery = Guest::where('event_id', $event->id);

        if (!empty($data['guest_ids'])) {
            $guestsQuery->whereIn('id', $data['guest_ids']);
        }

        if (!empty($data['resend_failed'])) {
            $failedGuestIds = SendInvitation::where('event_id', $event->id)
                ->where('status', SendInvitation::STATUS_FAILED)
                ->pluck('guest_id')
                ->toArray();

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
                $baseMessage = "Hola {$guest->name},\nEstás invitado al evento \"{$event->title}\" el día {$event->event_date->format('d/m/Y H:i')}.\nVerifica tu invitación: " . url('/invitations/'.$guest->invite_code);

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

                $overallStatus = SendInvitation::STATUS_SENT;

                // Email
                if ($channel === 'email' || $channel === 'both') {
                    if (!empty($guest->email)) {
                        try {
                            Mail::raw($baseMessage, function ($m) use ($guest, $event) {
                                $m->to($guest->email)
                                ->subject("Invitación: {$event->title}");
                            });
                        } catch (Throwable $e) {
                            $overallStatus = SendInvitation::STATUS_FAILED;
                        }
                    } else {
                        $overallStatus = SendInvitation::STATUS_FAILED;
                    }
                }

                $invitation->status = $overallStatus;
                $invitation->save();

                if ($overallStatus === SendInvitation::STATUS_SENT) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }

                $createdCount++;
            }

            DB::commit();

            // ✅ Si el canal incluye WhatsApp, dispara el envío real
            if ($channel === 'whatsapp' || $channel === 'both') {
                $this->sendAllWhatsApp($request);
            }

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
        \Log::info('>>> Entrando a sendAllWhatsApp', ['event_id' => $request->input('event_id')]);

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
                \Log::info('>>> Id invitado:', ['code' => $guest->invite_code]);

                // URL personalizada de confirmación
                $confirmationUrl = "https://devjgomez.com/eventos-app/invitations/{$guest->invite_code}";
                \Log::info('>>> enviando url invitacion', ['url' => $confirmationUrl]);

                // Procesar nombres de acompañantes
                $names = [$guest->name];
                if (!empty($guest->companions_names)) {
                    $companionsArray = array_map('trim', explode('|', $guest->companions_names));
                    $names = array_merge($names, $companionsArray);
                }

                // Formatear con comas y "y" antes del último
                if (count($names) > 1) {
                    $last = array_pop($names);
                    $fullNames = implode(', ', $names) . ' y ' . $last;
                } else {
                    $fullNames = $names[0];
                }

                // Reemplazar variables dinámicas
                $msg = str_replace(
                    ['{{NOMBRES_COMPLETOS}}', '{{URL}}'],
                    [$fullNames, $confirmationUrl],
                    $event->description
                );

                $response = Http::asJson()->post($baseUrl, [
                    'phone' => '57' . $guest->phone,
                    'message' => $msg
                ]);

                $status = $response->ok() ? 'sent' : 'failed';
                if ($status === 'sent') $sent++; else $failed++;

                SendInvitation::updateOrCreate(
                    ['event_id' => $eventId, 'guest_id' => $guest->id],
                    [
                        'channel' => 'whatsapp',
                        'status' => $status,
                        'message' => $msg
                    ]
                );

            } catch (\Throwable $th) {
                \Log::error('Error enviando mensaje WhatsApp', [
                    'guest' => $guest->id,
                    'error' => $th->getMessage()
                ]);
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
