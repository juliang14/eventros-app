@extends('layouts.app')

@section('content')
<div class="p-6">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Invitados 🎉</h1>

        <div class="flex gap-3">
            {{-- Botón para importar Excel --}}
            <form action="{{ route('guests.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="file" required class="border border-gray-300 rounded px-2 py-1 text-sm">
                <button type="submit" 
                        class="bg-green-700 text-white px-4 py-2 rounded-lg shadow hover:bg-green-800 transition">
                    📂 Importar Excel
                </button>
            </form>

            {{-- Botón para crear nuevo invitado --}}
            <a href="{{ route('guests.create') }}" 
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                ➕ Nuevo Invitado
            </a>
        </div>
    </div>

    {{-- Mensajes de estado --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 text-red-800 px-4 py-2 rounded">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- Tabla de invitados --}}
    @if($guests->count() > 0)
        <div class="overflow-x-auto bg-white rounded-xl shadow border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($guests as $guest)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $guest->id }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $guest->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $guest->email ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $guest->phone ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">
                                {{ $guest->event->title ?? 'Sin evento' }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    {{-- Editar --}}
                                    <a href="{{ route('guests.edit', $guest) }}" 
                                       class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                                        ✏️ Editar
                                    </a>
                                    {{-- Eliminar --}}
                                    <form action="{{ route('guests.destroy', $guest) }}" method="POST" 
                                          onsubmit="return confirm('¿Eliminar este invitado?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Mensaje si no hay invitados --}}
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-6 rounded-lg text-center mt-6 shadow-sm">
            <p class="text-lg font-semibold">😅 No hay invitados registrados.</p>
            <p class="text-sm text-yellow-700">Puedes agregar uno manualmente o importar un archivo Excel.</p>
        </div>
    @endif
</div>
@endsection
