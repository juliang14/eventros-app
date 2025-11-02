<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Event;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class GuestController extends Controller
{
    /**
     * Mostrar lista de invitados.
     */
    public function index()
    {
        $events = Event::all(['id', 'title']); // solo lo necesario
        $guests = Guest::all();

        return view('guests.index', compact('events', 'guests'));
    }

    /**
     * Mostrar formulario de creación de invitado.
     */
    public function create()
    {
        $events = Event::orderBy('title')->get();
        return view('guests.create', compact('events'));
    }

    /**
     * Guardar un invitado nuevo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:ev_events,id',
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Guest::create($request->all());

        return redirect()->route('guests.index')
            ->with('success', '🎉 Invitado registrado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Guest $guest)
    {
        $events = Event::orderBy('title')->get();
        return view('guests.edit', compact('guest', 'events'));
    }

    /**
     * Actualizar un invitado existente.
     */
    public function update(Request $request, Guest $guest)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:ev_events,id',
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $guest->update($request->all());

        return redirect()->route('guests.index')
            ->with('success', '✅ Invitado actualizado correctamente.');
    }

    /**
     * Eliminar un invitado.
     */
    public function destroy(Guest $guest)
    {
        $guest->delete();

        return redirect()->route('guests.index')
            ->with('success', '🗑️ Invitado eliminado correctamente.');
    }

    /**
     * Importar invitados desde archivo Excel (con respuesta JSON).
     */
    public function importarExcel(Request $request)
    {
        try {
            // ✅ Validación del formulario
            $request->validate([
                'event_id' => 'required|exists:ev_events,id',
                'excelFile' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $eventId = $request->event_id;
            $file = $request->file('excelFile');

            // ✅ Leemos el archivo completo
            $rows = Excel::toCollection(null, $file)[0];

            // Extraemos encabezados (primera fila) y limpiamos nombres
            $headers = $rows->shift()->map(fn($h) => strtolower(trim($h)));

            $insertados = 0;
            $duplicados = 0;
            $errores = 0;

            // ✅ Recorremos las filas restantes
            foreach ($rows as $row) {
                $data = $headers->combine($row); // Asocia encabezado → valor

                // Si no hay nombre, se omite la fila
                if (empty($data['name'])) continue;

                try {
                    // Evitar duplicados por correo + evento
                    $exists = Guest::where('email', $data['email'] ?? '')
                        ->where('event_id', $eventId)
                        ->exists();

                    if ($exists) {
                        $duplicados++;
                        continue;
                    }

                    // Crear invitado
                    Guest::create([
                        'name' => $data['name'],
                        'email' => $data['email'] ?? null,
                        'phone' => $data['phone'] ?? null,
                        'companions_names' => $data['companions_names'] ?? null,
                        'companions_count' => !empty($data['companions_names'])
                            ? count(explode('|', $data['companions_names']))
                            : 0,
                        'event_id' => $eventId,
                    ]);

                    $insertados++;
                } catch (\Exception $e) {
                    $errores++;
                }
            }

            // ✅ Respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => 'Importación completada correctamente',
                'total' => $rows->count(),
                'insertados' => $insertados,
                'duplicados' => $duplicados,
                'errores' => $errores,
            ]);
        } catch (\Exception $e) {
            // ⚠️ Manejo de errores
            return response()->json([
                'success' => false,
                'message' => 'Error durante la importación: ' . $e->getMessage(),
            ]);
        }
    }

}
