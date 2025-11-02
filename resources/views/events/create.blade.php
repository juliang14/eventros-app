@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white shadow rounded-xl p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Crear Evento</h1>

    <form action="{{ route('events.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Título</label>
            <input type="text" name="title" 
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Descripción</label>
            <textarea name="description" rows="3"
                      class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200"></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Fecha</label>
            <input type="datetime-local" name="event_date" 
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Ubicación</label>
            <input type="text" name="location" 
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200" required>
        </div>

        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
            💾 Guardar
        </button>
        <a href="{{ route('events.index') }}" 
           class="ml-2 text-gray-600 hover:underline">Cancelar</a>
    </form>
</div>
@endsection
