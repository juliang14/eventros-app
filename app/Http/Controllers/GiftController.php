<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GiftController extends Controller
{
    /**
     * Listado de regalos
     */
    public function index()
    {
        $gifts = Gift::with('event')->latest()->get();
        return view('gifts.index', compact('gifts'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $events = Event::all();
        return view('gifts.create', compact('events'));
    }

    /**
     * Guardar nuevo regalo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:ev_events,id',
            'name' => 'required|string|max:255',
            'is_required' => 'nullable|boolean',
            'hide_when_reserved' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        // Ajustar cantidad según tipo
        $validated['is_required'] = $request->has('is_required');
        $validated['hide_when_reserved'] = $request->has('hide_when_reserved');

        if ($validated['is_required']) {
            $validated['quantity'] = 999999999;
        } else {
            $validated['quantity'] = $request->input('quantity', 1);
        }

        $validated['reserved_count'] = 0;
        $validated['is_reserved'] = false;
        $validated['reserved_by'] = null;

        // Subir imagen o asignar por defecto
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('gifts', 'public');
            $validated['image_path'] = 'storage/' . $path;
        } else {
            $validated['image_path'] = asset('images/default_gift.png');
        }

        Gift::create($validated);

        return redirect()->route('gifts.index')
            ->with('success', 'Regalo agregado correctamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit(Gift $gift)
    {
        $events = Event::all();
        return view('gifts.edit', compact('gift', 'events'));
    }

    /**
     * Actualizar regalo
     */
    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:ev_events,id',
            'name' => 'required|string|max:255',
            'is_required' => 'nullable|boolean',
            'hide_when_reserved' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_required'] = $request->has('is_required');
        $validated['hide_when_reserved'] = $request->has('hide_when_reserved');

        // Reglas automáticas
        if ($validated['is_required']) {
            $validated['quantity'] = 999999999;
        } else {
            $validated['quantity'] = $request->input('quantity', 1);
        }

        // Manejar imagen (si se reemplaza)
        if ($request->hasFile('image')) {
            if ($gift->image_path && str_contains($gift->image_path, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $gift->image_path));
            }
            $path = $request->file('image')->store('gifts', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        $gift->update($validated);

        return redirect()->route('gifts.index')
            ->with('success', 'Regalo actualizado correctamente.');
    }

    /**
     * Eliminar regalo
     */
    public function destroy(Gift $gift)
    {
        if ($gift->image_path && str_contains($gift->image_path, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $gift->image_path));
        }

        $gift->delete();
        return redirect()->route('gifts.index')
            ->with('success', 'Regalo eliminado correctamente.');
    }
}
