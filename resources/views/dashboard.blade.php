@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Bienvenida --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold">¡Bienvenido, {{ Auth::user()->name }}!</h1>
        <p class="text-muted">
            Aquí tienes un resumen de tus próximos eventos y acciones rápidas.
        </p>
    </div>

    {{-- Acciones rápidas --}}
    <div class="row text-center mb-5">
        <div class="col-md-3 mb-3">
            <a href="{{ route('events.create') }}" class="quick-action">
                <i class="bi bi-plus-circle fs-1 mb-2"></i>
                <p>Crear Evento</p>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('guests.index') }}" class="quick-action">
                <i class="bi bi-people fs-1 mb-2"></i>
                <p>Gestionar Invitados</p>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('gifts.index') }}" class="quick-action">
                <i class="bi bi-gift fs-1 mb-2"></i>
                <p>Listas de Regalos</p>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="#" class="quick-action">
                <i class="bi bi-question-circle fs-1 mb-2"></i>
                <p>Ayuda y Soporte</p>
            </a>
        </div>
    </div>

    {{-- Próximos eventos --}}
    <h3 class="fw-bold mb-4">Próximos Eventos</h3>

    @if ($events->isEmpty())
        <div class="alert alert-info">
            No tienes eventos próximos.
        </div>
    @else
        <div class="row">
            @foreach ($events as $event)
                <div class="col-md-6 mb-4">
                    <div class="card event-card shadow-sm border-0 rounded-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                {{-- Imagen temporal (puedes guardar un campo en la BD si quieres) --}}
                                <img src="https://via.placeholder.com/300x200.png?text=Evento"
                                     class="img-fluid h-100 rounded-start-4" alt="Evento">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <span class="badge bg-pink mb-2">Evento</span>
                                    <h5 class="card-title fw-bold">{{ $event->title }}</h5>
                                    <p class="card-text text-muted mb-1">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('d \d\e F, Y') }}
                                    </p>
                                    <p class="card-text text-muted mb-1">
                                        <i class="bi bi-clock"></i>
                                        {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                                    </p>
                                    <p class="card-text text-muted mb-2">
                                        <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                    </p>
                                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-pink btn-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Estilos custom --}}
<style>
    .quick-action {
        display: block;
        background: #fce4ec;
        padding: 20px;
        border-radius: 15px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
    }
    .quick-action:hover {
        background: #f8bbd0;
        color: #000;
    }
    .event-card {
        background: #fff;
    }
    .bg-pink {
        background-color: #f48fb1 !important;
    }
    .btn-pink {
        background-color: #ec407a;
        color: #fff;
        border-radius: 25px;
    }
    .btn-pink:hover {
        background-color: #d81b60;
        color: #fff;
    }
</style>
@endsection
