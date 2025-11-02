@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
    $today = Carbon::today();

    // Clasificamos eventos según fecha
    $upcomingEvents = $events->filter(fn($event) => Carbon::parse($event->event_date)->isAfter($today));
    $pastEvents = $events->filter(fn($event) => Carbon::parse($event->event_date)->isBefore($today->addDay()));
@endphp

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold text-dark mb-0">📅 Eventos</h2>
        <a href="{{ route('events.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Crear Evento
        </a>
    </div>

    {{-- Tabs tipo carpeta --}}
    <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" 
                    type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                Próximos Eventos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" 
                    type="button" role="tab" aria-controls="past" aria-selected="false">
                Eventos Pasados
            </button>
        </li>
    </ul>

    <div class="tab-content" id="eventTabsContent">
        {{-- Próximos eventos --}}
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
            @if($upcomingEvents->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    <p class="fs-5">No hay próximos eventos programados.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach($upcomingEvents as $event)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title text-primary fw-bold">{{ $event->title }}</h5>
                                    <p class="card-text text-muted">{{ $event->description }}</p>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-event"></i>
                                        <strong>{{ Carbon::parse($event->event_date)->format('d/m/Y h:i A') }}</strong>
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('events.edit', $event->id) }}" class="btn btn-warning btn-sm text-white">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                        <form action="{{ route('events.destroy', $event->id) }}" method="POST" 
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este evento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash3"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Eventos pasados --}}
        <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
            @if($pastEvents->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                    <p class="fs-5">Aún no hay eventos pasados registrados.</p>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach($pastEvents as $event)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-secondary fw-bold">{{ $event->title }}</h5>
                                    <p class="card-text text-muted">{{ $event->description }}</p>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-event"></i>
                                        <strong>{{ Carbon::parse($event->event_date)->format('d/m/Y h:i A') }}</strong>
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                        <form action="{{ route('events.destroy', $event->id) }}" method="POST" 
                                              onsubmit="return confirm('¿Seguro que deseas eliminar este evento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash3"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
