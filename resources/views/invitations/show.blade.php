<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }} - Invitación</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50">

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-6 mt-10">
    <h1 class="text-2xl font-bold text-center text-pink-600 mb-4">{{ $event->title }}</h1>
    <p class="text-center text-gray-700">{{ $event->description }}</p>

    <div class="mt-4 text-center">
        <p><strong>Fecha:</strong> {{ $event->event_date->format('d M Y H:i') }}</p>
        <p><strong>Lugar:</strong> {{ $event->location }}</p>
    </div>

    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('invitacion.rsvp', $guest->invite_code) }}">
            @csrf
            <button name="status" value="confirmed" class="bg-green-500 text-white px-4 py-2 rounded-lg">✅ Asistiré</button>
            <button name="status" value="declined" class="bg-red-500 text-white px-4 py-2 rounded-lg">❌ No podré</button>
        </form>
    </div>
</div>

</body>
</html>
