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
        $guests = Guest::with('event')->latest()->get();

        return view('guests.index', compact('guests'));
    }

    /**
     * Mostrar formulario de creación de invitado.
     */
    public function create()
    {
        $events = Event::orderBy('name')->get();
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
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
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
        $events = Event::orderBy('name')->get();
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
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
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
     * Importar invitados desde Excel.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        $count = 0;

        foreach ($data[0] as $row) {
            if (!empty($row[0])) {
                Guest::create([
                    'event_id' => $row[0], // ID del evento
                    'name'     => $row[1] ?? 'Invitado sin nombre',
                    'email'    => $row[2] ?? null,
                    'phone'    => $row[3] ?? null,
                ]);
                $count++;
            }
        }

        return redirect()->route('guests.index')
                         ->with('success', "📥 Se importaron {$count} invitados correctamente.");
    }
}
