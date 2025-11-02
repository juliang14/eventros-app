@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto bg-white rounded-xl shadow border border-gray-200">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center"> Nuevo Invitado</h1>

    {{-- Mensajes de error --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-100 text-red-800 px-4 py-2 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="guestForm" action="{{ route('guests.store') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Nombre --}}
        <div>
            <label for="name" class="block text-gray-700 font-medium">Nombre del invitado *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
        </div>

        {{-- Correo --}}
        <div>
            <label for="email" class="block text-gray-700 font-medium">Correo electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
        </div>

        {{-- Teléfono --}}
        <div>
            <label for="phone" class="block text-gray-700 font-medium">Teléfono</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
        </div>

        {{-- Evento --}}
        <div>
            <label for="event_id" class="block text-gray-700 font-medium">Evento *</label>
            <select name="event_id" id="event_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
                <option value="">-- Selecciona un evento --</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                        {{ $event->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Acompañantes --}}
        <div class="mb-5">
            <label class="block text-gray-700 font-medium mb-2">Acompañantes</label>

            <div id="companionsList" class="space-y-2"></div>

            <button type="button" id="addCompanionBtn"
                class="bg-green-600 text-black px-3 py-1 rounded hover:bg-green-700 transition">
                ➕ Agregar acompañante
            </button>

            {{-- Campos ocultos que se envían al backend --}}
            <input type="hidden" name="companions_names" id="companions_names">
            <input type="hidden" name="companions_count" id="companions_count">
        </div>

        {{-- Botones --}}
        <div class="flex justify-between mt-5">
            <button type="submit" 
                    class="btn btn-primary text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">
                💾 Guardar Invitado
            </button>    
            <a href="{{ route('guests.index') }}" 
               class="btn btn-secondary text-white-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                ⬅️ Cancelar
            </a>
        </div>
    </form>
</div>

{{-- Script dinámico --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('addCompanionBtn');
    const list = document.getElementById('companionsList');
    const form = document.getElementById('guestForm');
    let counter = 0;

    addBtn.addEventListener('click', () => {
        counter++;
        const div = document.createElement('div');
        div.className = "flex items-center gap-2";
        div.innerHTML = `
            <input type="text" name="companion[]" placeholder="Nombre del acompañante ${counter}"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            <button type="button" class="removeCompanion bg-red-500 text-white px-2 py-1 rounded">✖</button>
        `;
        list.appendChild(div);

        // Eliminar acompañante
        div.querySelector('.removeCompanion').addEventListener('click', () => {
            div.remove();
            counter--;
        });
    });

    form.addEventListener('submit', (e) => {
        const companions = Array.from(document.querySelectorAll('input[name="companion[]"]'))
            .map(input => input.value.trim())
            .filter(name => name !== '');

        // Rellenar los campos ocultos
        document.getElementById('companions_names').value = companions.join('|');
        document.getElementById('companions_count').value = companions.length;
    });
});
</script>
@endsection
