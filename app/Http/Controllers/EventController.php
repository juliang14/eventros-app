<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Dashboard principal
    public function dashboard()
    {
        $events = Event::where('event_date', '>=', now())
                       ->orderBy('event_date')
                       ->get();

        return view('dashboard', compact('events'));
    }

    public function index()
    {
        $events = Event::all();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'event_date' => 'required|date',
            'location' => 'required',
        ]);

        Event::create($request->all());

        return redirect()->route('events.index')
                         ->with('success', 'Evento creado correctamente.');
    }

    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required',
            'event_date' => 'required|date',
            'location' => 'required',
        ]);

        $event->update($request->all());

        return redirect()->route('events.index')
                         ->with('success', 'Evento actualizado.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')
                         ->with('success', 'Evento eliminado.');
    }
}
