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
            background: linear-gradient(180deg, #e0f2ff 0%, #f8fbff 100%);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .invitation-card {
            max-width: 780px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
            margin: 30px auto;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #a9d6f5, #bde0fe);
            color: #004080;
            text-align: center;
            padding: 30px 20px;
        }

        .header h1 {
            font-weight: 700;
            font-size: 1.9rem;
        }

        .event-info {
            background: #f7fbff;
            border-radius: 12px;
            padding: 15px;
            margin: 20px auto;
            font-size: 0.95rem;
        }

        .event-info i {
            color: #0d6efd;
            margin-right: 6px;
        }

        .description {
            font-size: 1rem;
            color: #4a4a4a;
            margin: 20px 0;
            line-height: 1.6;
        }

        .gifts-section h5 {
            color: #004080;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }

        .gifts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .gift-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 14px;
            padding: 10px;
            text-align: center;
            transition: all .3s ease;
            font-size: 0.9rem;
        }

        .gift-card:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            transform: scale(1.03);
        }

        .gift-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .gift-card.disabled {
            opacity: 0.7;
            background: #f8f9fa;
            border-style: dashed;
            pointer-events: none;
        }

        .badge-qty {
            background-color: #e3f2fd;
            color: #0d6efd;
            border-radius: 8px;
            padding: 3px 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-confirm {
            position: relative;
            background-color: #0d6efd;
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 22px;
            overflow: hidden;
            transition: all .3s ease;
        }

        .btn-confirm:hover {
            background-color: #0b5ed7;
            transform: scale(1.05);
        }

        .btn-decline {
            border: 1.5px solid #dc3545;
            color: #dc3545;
            background: #fff;
            border-radius: 10px;
            padding: 10px 22px;
            transition: all .3s ease;
        }

        .btn-decline:hover {
            background-color: #dc3545;
            color: #fff;
            transform: scale(1.05);
        }

        .confirmation-message {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .confirmation-message.success {
            background-color: #e0f7fa;
            color: #006064;
        }

        .confirmation-message.danger {
            background-color: #fdecea;
            color: #b71c1c;
        }

        footer {
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 20px;
        }
        /* 🖱️ Mostrar cursor tipo mano cuando sea seleccionable */
        .gift-card {
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }

        /* ✨ Cuando el checkbox esté marcado */
        .gift-card input[type="checkbox"]:checked + img,
        .gift-card input[type="checkbox"]:checked ~ .gift-details {
            border-color: #0d6efd;
        }

        /* 🌟 Alternativamente, aplicar el borde al contenedor completo */
        .gift-card input[type="checkbox"]:checked {
            outline: none;
        }

        .gift-card:has(input[type="checkbox"]:checked) {
            border: 2px solid #0d6efd;
            box-shadow: 0 0 8px rgba(13, 110, 253, 0.4);
            transform: scale(1.03);
        }

        .gift-card:not(.disabled):hover {
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.15);
            transform: scale(1.02);
        }

    </style>
</head>
<body>

<div class="container">
    <div class="invitation-card shadow-lg">
        <div class="header">
            <h1>{{ $event->title }}</h1>
            @if(!empty($guest->name))
                <p class="mb-0 guest-name">Hola {{ $guest->name }} 👋</p>
            @endif
            <p class="mb-0">💙 ¡Te esperamos para celebrar juntos la llegada de Julián Esteban! 💙</p>
        </div>

        <div class="card-body p-4">

            <div class="event-info">
                <table class="w-100">
                    <tr>
                        <td><i class="bi bi-calendar-event"></i> <strong>Fecha:</strong></td>
                        <td><strong>{{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y h:i A') }}</strong></td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-geo-alt"></i> <strong>Lugar:</strong></td>
                        <td>{{ $event->location }}</td>
                    </tr>
                </table>
            </div>

            {{-- 💬 Mensaje dinámico según estado --}}
            <div id="confirmationMessages">
                @if($guest->confirmed == 1)
                    <div id="msgConfirmed" class="confirmation-message success">
                        🎉 ¡Estamos felices de que hayas confirmado tu asistencia! 💙
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="btnModify">Modificar respuesta</button>
                        </div>
                    </div>
                @elseif($guest->confirmed == 2)
                    <div id="msgDeclined" class="confirmation-message danger">
                        😢 Lamentamos que no puedas acompañarnos. 💔
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="btnModify">Modificar respuesta</button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 💝 Sección de regalos (solo visible si confirmed == 0 o al modificar respuesta) --}}
            <div id="giftsForm" class="{{ $guest->confirmed != 0 ? 'd-none' : '' }}">
                <div class="description text-center">
                    Nos encantaría contar contigo en este día tan especial 🌟.  
                    Si deseas compartir un detalle, puedes ayudarnos con alguno de los regalos de la lista 🎁.  
                    ¡No te sientas comprometido! Tu presencia es lo más importante 💙.
                </div>

                <form method="POST" action="{{ route('invitations.rsvp', $guest->invite_code) }}">
                    @csrf
                    <div class="gifts-section">
                        <h5>🎁 Selecciona tu(s) regalo(s)</h5>
                        {{-- 💬 Mensaje si ya confirmó asistencia --}}
                        @if($guest->confirmed > 0)
                            <div class="alert alert-info text-center mt-2" style="font-size: 0.9rem;">
                                <i class="bi bi-info-circle"></i>
                                Si deseas <strong>agregar o quitar regalos</strong>, presiona nuevamente el botón 
                                <strong>"Asistiré"</strong> para actualizar tu selección. 🎁
                            </div>

                            <div class="alert alert-warning text-center mt-2" style="font-size: 0.9rem;">
                                <i class="bi bi-exclamation-triangle"></i>
                                Si decides <strong>no asistir</strong>, tus regalos seleccionados se liberarán automáticamente 
                                para que otros invitados puedan elegirlos. 💙
                            </div>
                        @endif
                        <div class="gifts-grid">
                            @foreach($gifts as $gift)
                                @php
                                    $availableQty = max(0, $gift->quantity - $gift->reserved_count);
                                    $available = $availableQty > 0;
                                    $isMandatory = (bool) ($gift->is_required ?? $gift->is_mandatory ?? false);
                                    $imagePath = $gift->image_path ? asset($gift->image_path) : asset('images/default_gift.png');

                                    // 🔹 Nuevo: saber si el invitado actual reservó este regalo
                                    $reservedBy = $gift->reserved_by ? explode('|', $gift->reserved_by) : [];
                                    $isReservedByGuest = in_array($guest->id, $reservedBy);
                                @endphp

                                {{-- 🔹 Mostrar condiciones según disponibilidad y reservas --}}
                                @if($available || $isMandatory || ($guest->confirmed == 1 && $isReservedByGuest))
                                    <label class="gift-card {{ $isMandatory ? 'disabled' : '' }}">
                                        <input 
                                            type="checkbox" 
                                            name="gifts[]" 
                                            value="{{ $gift->id }}"
                                            class="form-check-input me-2"
                                            {{-- Regla de selección según caso --}}
                                            @if($isMandatory)
                                                checked disabled
                                            @elseif($isReservedByGuest)
                                                checked {{-- Puede desmarcar --}}
                                            @elseif(!$available)
                                                disabled {{-- Sin stock y no reservado por él --}}
                                            @endif
                                        >

                                        <img src="{{ $imagePath }}" alt="{{ $gift->name }}">
                                        <div class="gift-details">
                                            <strong>{{ $gift->name }}</strong>
                                            @if($gift->description)
                                                <small>{{ $gift->description }}</small>
                                            @endif

                                            {{-- Etiquetas informativas --}}
                                            @if($isMandatory)
                                                <span class="badge bg-secondary mt-1">Incluido 🎁</span>
                                            @elseif($isReservedByGuest && !$available)
                                                <span class="badge bg-warning text-dark mt-1">Reservado por ti 💙</span>
                                            @else
                                                <span class="badge-qty mt-1">{{ $availableQty }} disponibles</span>
                                            @endif
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="status" value="confirmed" class="btn btn-confirm me-2" id="btnConfirm">
                            Asistiré
                        </button>
                        <button type="submit" name="status" value="declined" class="btn btn-decline">No podré</button>
                    </div>
                </form>
            </div>

        </div>

        <footer class="pb-3">
            Con cariño, <strong>Carolay y Julián 💙</strong>
        </footer>
    </div>
</div>

<script>
    // 🎊 Confetti efecto
    const btnConfirm = document.getElementById('btnConfirm');
    if (btnConfirm) {
        btnConfirm.addEventListener('mouseenter', () => makeConfetti(btnConfirm));
        btnConfirm.addEventListener('click', () => makeConfetti(btnConfirm));
    }

    function makeConfetti(button) {
        for (let i = 0; i < 20; i++) {
            const confetti = document.createElement('span');
            confetti.classList.add('confetti');
            confetti.style.position = 'absolute';
            confetti.style.width = '6px';
            confetti.style.height = '6px';
            confetti.style.borderRadius = '50%';
            confetti.style.backgroundColor = randomColor();
            confetti.style.left = `${Math.random() * 100}%`;
            confetti.style.animation = 'fall 1.2s linear forwards';
            confetti.style.opacity = '0';
            button.appendChild(confetti);
            setTimeout(() => confetti.remove(), 1200);
        }
    }

    function randomColor() {
        const colors = ['#ff4d4d', '#ffd633', '#66ff66', '#66b3ff', '#ff80d5', '#ffa64d'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    // ✨ Mostrar formulario al presionar "Modificar respuesta"
    const btnModify = document.getElementById('btnModify');
    if (btnModify) {
        btnModify.addEventListener('click', () => {
            document.getElementById('giftsForm').classList.remove('d-none');
            document.getElementById('confirmationMessages').classList.add('d-none');
        });
    }
</script>

</body>
</html>
