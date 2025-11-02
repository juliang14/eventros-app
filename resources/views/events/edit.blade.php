@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Evento</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('events.update', $event->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Título</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="event_date" class="form-label">Fecha del Evento</label>
            <input type="date" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date) }}" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Ubicación</label>
            <input type="text" name="location" class="form-control" value="{{ old('location', $event->location) }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" class="form-control">{{ old('description', $event->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
