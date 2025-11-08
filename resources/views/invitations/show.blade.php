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

        .event-info table {
            width: 100%;
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

        /* 🎁 Sección de regalos */
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
            width: 100%;
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

        .gift-details strong {
            color: #004080;
            display: block;
            margin-bottom: 4px;
        }

        .gift-details small {
            color: #6c757d;
            display: block;
            font-size: 0.8rem;
        }

        /* 🎉 Botones con animaciones */
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

        /* 🎊 Confeti animado realista */
        .btn-confirm .confetti {
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            animation: fall 1.2s linear forwards;
            opacity: 0;
        }

        @keyframes fall {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(40px) scale(0.8);
                opacity: 0;
            }
        }

        /* 😢 Carita triste */
        .btn-decline {
            border: 1.5px solid #dc3545;
            color: #dc3545;
            background: #fff;
            border-radius: 10px;
            padding: 10px 22px;
            position: relative;
            transition: all .3s ease;
        }

        .btn-decline::after {
            content: '😢';
            position: absolute;
            opacity: 0;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            transition: opacity .3s ease;
        }

        .btn-decline:hover::after {
            opacity: 1;
        }

        .btn-decline:hover {
            background-color: #dc3545;
            color: #fff;
            transform: scale(1.05);
        }

        footer {
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 20px;
        }

        @media (max-width: 576px) {
            .header h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="invitation-card shadow-lg">
        <div class="header">
            <h1>{{ $event->title }}</h1>
            <p class="mb-0">💙 ¡Te esperamos para celebrar juntos la llegada de Julián Esteban! 💙</p>
        </div>

        <div class="card-body p-4">
            <div class="event-info">
                <table>
                    <tr>
                        <td><i class="bi bi-calendar-event"></i> <strong>Fecha:</strong></td>
                        <td>{{ $event->event_date->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><i class="bi bi-geo-alt"></i> <strong>Lugar:</strong></td>
                        <td>{{ $event->location }}</td>
                    </tr>
                </table>
            </div>

            <div class="description text-center">
                Nos encantaría contar contigo en este día tan especial 🌟.  
                Si deseas compartir un detalle, puedes ayudarnos con alguno de los regalos de la lista 🎁.  
                ¡No te sientas comprometido! Tu presencia es lo más importante 💙.
            </div>

            <form method="POST" action="{{ route('invitations.rsvp', $guest->invite_code) }}">
                @csrf

                <div class="gifts-section">
                    <h5>🎁 Selecciona tu(s) regalo(s)</h5>
                    <div class="gifts-grid">
                        @foreach($gifts as $gift)
                            @php
                                $available = ($gift->quantity - $gift->reserved_count) > 0;
                                $isMandatory = (bool) ($gift->is_required ?? $gift->is_mandatory ?? false);
                            @endphp

                            @if($available || $isMandatory)
                                @php
                                    $imagePath = !empty($gift->image_path)
                                        ? asset('storage/invitations/' . $gift->image_path)
                                        : asset('images/default_gift.png');
                                @endphp

                                <label class="gift-card {{ $isMandatory ? 'disabled' : '' }}">
                                    <input type="checkbox" name="gifts[]" value="{{ $gift->id }}"
                                           class="form-check-input me-2"
                                           {{ $isMandatory ? 'checked disabled' : '' }}>
                                    <img src="{{ $imagePath }}" alt="{{ $gift->name }}">
                                    <div class="gift-details">
                                        <strong>{{ $gift->name }}</strong>
                                        @if($gift->description)
                                            <small>{{ $gift->description }}</small>
                                        @endif
                                        @if($isMandatory)
                                            <span class="badge bg-secondary mt-1">Incluido 🎁</span>
                                        @endif
                                    </div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" name="status" value="confirmed" class="btn btn-confirm me-2" id="btnConfirm">
                        Asistiré
                    </button>
                    <button name="status" value="declined" class="btn btn-decline">No podré</button>
                </div>
            </form>
        </div>

        <footer class="pb-3">
            Con cariño, <strong>Dayana y Julián 💙</strong>
        </footer>
    </div>
</div>

<script>
    // 🎊 Efecto confeti simple en hover y clic del botón "Asistiré"
    const btnConfirm = document.getElementById('btnConfirm');
    btnConfirm.addEventListener('mouseenter', () => makeConfetti(btnConfirm));
    btnConfirm.addEventListener('click', () => makeConfetti(btnConfirm));

    function makeConfetti(button) {
        for (let i = 0; i < 15; i++) {
            const confetti = document.createElement('span');
            confetti.classList.add('confetti');
            confetti.style.left = `${Math.random() * 100}%`;
            confetti.style.backgroundColor = randomColor();
            confetti.style.animationDelay = `${Math.random() * 0.5}s`;
            button.appendChild(confetti);
            setTimeout(() => confetti.remove(), 1200);
        }
    }

    function randomColor() {
        const colors = ['#ff4d4d', '#ffd633', '#66ff66', '#66b3ff', '#ff80d5', '#ffa64d'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
</script>

</body>
</html>
