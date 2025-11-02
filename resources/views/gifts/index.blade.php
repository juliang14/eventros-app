<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Lista de Regalos</h2>

        <form action="{{ route('events.gifts.store', $event) }}" method="POST" class="mb-6 space-y-4">
            @csrf
            <div>
                <label class="block">Nombre del regalo</label>
                <input type="text" name="name" class="w-full border rounded-lg p-2" required>
            </div>
            <div>
                <label class="block">Descripción</label>
                <input type="text" name="description" class="w-full border rounded-lg p-2">
            </div>
            <button class="bg-pink-500 text-white px-4 py-2 rounded-lg">Agregar</button>
        </form>

        <table class="w-full border-collapse bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <thead>
                <tr>
                    <th class="p-2">Regalo</th>
                    <th class="p-2">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gifts as $gift)
                <tr>
                    <td class="p-2">{{ $gift->name }}</td>
                    <td class="p-2">{{ $gift->is_taken ? 'Reservado' : 'Disponible' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
