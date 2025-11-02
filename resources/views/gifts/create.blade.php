@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <h4 class="text-center fw-bold mb-4 text-secondary">Agregar Regalo</h4>

            <form action="{{ route('gifts.store') }}" method="POST" enctype="multipart/form-data" id="giftForm">
                @csrf

                {{-- Campo oculto para el comportamiento por defecto --}}
                <input type="hidden" name="hide_when_reserved" value="1">

                {{-- EVENTO --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Evento</label>
                    <select name="event_id" class="form-select" required>
                        <option value="" disabled selected>Seleccione un evento</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- NOMBRE --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre del regalo</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ej. Cafetera de cápsulas">
                </div>

                {{-- CANTIDAD --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad</label>
                    <input type="number" id="quantity" name="quantity" class="form-control cantidad-bloqueada" 
                        value="1" min="1" readonly>
                    <div class="form-text text-muted">
                        Este campo se desbloqueará si se permiten reservas.
                    </div>
                </div>

                {{-- CHECKS --}}
                <div class="mb-3 d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                        <label class="form-check-label fw-semibold" for="is_required">
                            Obligatorio (aplicará para todos los invitados)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="allow_partial" name="allow_partial" value="1">
                        <label class="form-check-label fw-semibold" for="allow_partial">
                            Permitir reservas (se ocultará cuando alguien lo tome)
                        </label>
                    </div>
                </div>

                {{-- IMAGEN --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Imagen del regalo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text">Si no seleccionas una imagen, se usará una por defecto.</div>
                </div>

                {{-- BOTONES --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('gifts.index') }}" class="btn btn-secondary me-2 px-4">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save me-2"></i> Guardar Regalo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ESTILOS ADICIONALES --}}
<style>
    /* Simula el "readonly" visualmente */
    input[readonly].cantidad-bloqueada {
        background-color: #e9ecef !important;
        cursor: not-allowed;
        color: #6c757d;
        opacity: 1;
    }

    /* Centrado sobrio del título */
    h4 {
        letter-spacing: 0.3px;
    }
</style>

{{-- SCRIPT DE LÓGICA DINÁMICA --}}
<script>
document.addEventListener("DOMContentLoaded", () => {
    const isRequired = document.getElementById("is_required");
    const allowPartial = document.getElementById("allow_partial");
    const quantity = document.getElementById("quantity");
    const form = document.getElementById("giftForm");

    function actualizarCantidad() {
        if (isRequired.checked) {
            quantity.value = 999999999;
            quantity.readOnly = true;
            quantity.classList.add('cantidad-bloqueada');
            allowPartial.checked = false;
            allowPartial.disabled = true;
        } else if (allowPartial.checked) {
            quantity.readOnly = false;
            quantity.classList.remove('cantidad-bloqueada');
            if (quantity.value == 999999999) quantity.value = 1;
        } else {
            quantity.value = 1;
            quantity.readOnly = true;
            quantity.classList.add('cantidad-bloqueada');
            allowPartial.disabled = false;
        }
    }

    isRequired.addEventListener("change", actualizarCantidad);
    allowPartial.addEventListener("change", actualizarCantidad);
    actualizarCantidad();

    // Validación antes de enviar
    form.addEventListener("submit", (e) => {
        const qty = parseInt(quantity.value);

        if (!isRequired.checked && !allowPartial.checked && qty !== 1) {
            e.preventDefault();
            alert("Si el regalo no es obligatorio ni permite reservas, la cantidad debe ser 1.");
        }
        if (allowPartial.checked && qty < 1) {
            e.preventDefault();
            alert("Debe ingresar una cantidad válida si permite reservas.");
        }
    });
});
</script>
@endsection
