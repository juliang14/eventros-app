@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Eventos</h1>
        <a href="{{ route('events.create') }}" 
        class="inline-flex items-center gap-2 bg-blue-600 text-back px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            <span class="text-lg">➕</span>
            <span>Crear Evento</span>
        </a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        @foreach($events as $event)
            <div class="bg-white shadow rounded-xl p-5 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-700">{{ $event->title }}</h2>
                <p class="text-sm text-gray-500 mb-2">{{ $event->description }}</p>

                <div class="text-sm text-gray-600">
                    📅 <strong>{{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y H:i') }}</strong><br>
                    📍 {{ $event->location }}
                </div>

                <div class="flex justify-between items-center mt-4">
                    <a href="{{ route('events.edit', $event->id) }}" 
                       class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                       ✏️ Editar
                    </a>
                    
                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este evento?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                            ❌ Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
