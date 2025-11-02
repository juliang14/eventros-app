<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }} - Invitación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            border-radius: 18px;
        }
        .event-info p {
            margin-bottom: .4rem;
        }
        .gift-card {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all .3s ease;
        }
        .gift-card:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            transform: scale(1.01);
        }
        .gift-card img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
        }
        .gift-card.disabled {
            opacity: .6;
            pointer-events: none;
            background-color: #f5f5f5;
        }
        .gift-card .gift-details {
            flex-grow: 1;
            font-size: 0.95rem;
        }
        .btn-confirm, .btn-decline {
            padding: .5rem 1.2rem;
            font-size: 1rem;
            border-radius: 8px;
        }
        .btn-confirm {
            background-color: #198754;
            border: none;
            color: #fff;
        }
        .btn-confirm:hover {
            background-color: #157347;
        }
        .btn-decline {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: #fff;
        }
        .btn-decline:hover {
            background-color: #dc3545;
            color: #fff;
        }
        @media (max-width: 576px) {
            .gift-card {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            .gift-card img {
                width: 100%;
                height: auto;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="card shadow-lg border-0 mx-auto p-4" style="max-width: 750px;">
        <div class="card-body text-center">
            <h1 class="fw-bold text-primary mb-3">{{ $event->title }}</h1>
            <p class="text-muted mb-4">{{ $event->description }}</p>

            <div class="row justify-content-center event-info mb-4 text-muted small">
                <div class="col-6 text-end">
                    <p><i class="bi bi-calendar-event me-1"></i>
                        <strong>{{ $event->event_date->format('d/m/Y H:i') }}</strong></p>
                </div>
                <div class="col-6 text-start">
                    <p><i class="bi bi-geo-alt me-1"></i> {{ $event->location }}</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif

            @php
                $rsvp = \App\Models\Rsvp::where('guest_id', $guest->id)->first();
                $status = $rsvp->status ?? null;
            @endphp

            @if($status)
                <div class="alert alert-info text-center mb-4">
                    @if($status === 'confirmed')
                        <i class="bi bi-check-circle-fill text-success"></i>
                        Has confirmado tu asistencia 🎉.
                    @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        Has indicado que no podrás asistir 😔.
                    @endif
                    <br>
                    <small class="text-muted">
                        Si deseas cambiar tu respuesta, haz clic en el botón de abajo.
                    </small>
                </div>
            @endif

            <form method="POST" action="{{ route('invitations.rsvp', $guest->invite_code) }}">
                @csrf

                @if(!$status || session('allow_change'))
                    <h5 class="fw-semibold text-secondary mb-3">
                        🎁 Selecciona tu(s) regalo(s)
                    </h5>

                    <div class="list-group text-start mb-4">
                        @php
                            $sortedGifts = $gifts->sortByDesc(function($gift) {
                                return $gift->is_required ?? $gift->is_mandatory ?? false;
                            });
                        @endphp

                        @forelse($sortedGifts as $gift)
                            @php
                                $available = ($gift->quantity - $gift->reserved_count) > 0;
                                $isMandatory = (bool) ($gift->is_required ?? $gift->is_mandatory ?? false);

                                if (!empty($gift->image_path)) {
                                    if (Str::contains($gift->image_path, ['invitations/', 'gifts/', 'storage/'])) {
                                        $imagePath = asset($gift->image_path);
                                    } else {
                                        $imagePath = asset('storage/invitations/' . $gift->image_path);
                                    }
                                } else {
                                    $imagePath = asset('images/no-image.png');
                                }
                            @endphp

                            <label class="gift-card mb-2 {{ (!$available || $isMandatory) ? 'disabled' : '' }}">
                                <input type="checkbox"
                                    name="gifts[]"
                                    value="{{ $gift->id }}"
                                    class="form-check-input me-2"
                                    {{ $isMandatory ? 'checked disabled' : '' }}
                                    {{ !$available && !$isMandatory ? 'disabled' : '' }}>
                                <img src="{{ $imagePath }}" alt="{{ $gift->name }}" onerror="this.src='{{ asset('images/default_gift.png') }}'">
                                <div class="gift-details">
                                    <strong>{{ $gift->name }}</strong><br>
                                    @if(!empty($gift->description))
                                        <small class="text-muted">{{ $gift->description }}</small><br>
                                    @endif
                                    @if($isMandatory)
                                        <span class="badge bg-secondary mt-1">Obligatorio</span>
                                    @elseif(!$available)
                                        <span class="badge bg-danger mt-1">Agotado</span>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <p class="text-muted text-center">No hay regalos disponibles para este evento.</p>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button name="status" value="confirmed" class="btn btn-confirm text-white">
                            Asistiré
                        </button>
                        <button name="status" value="declined" class="btn btn-decline">
                            No podré
                        </button>
                    </div>
                @else
                    <div class="d-flex justify-content-center mt-3">
                        <a href="{{ route('invitations.show', $guest->invite_code) }}?change=1"
                           class="btn btn-outline-primary">
                            Cambiar mi respuesta
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

</body>
</html>
