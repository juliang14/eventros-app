@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <h4 class="text-center fw-bold mb-4 text-secondary">Agregar Regalo</h4>

            <form action="{{ route('gifts.store') }}" method="POST" enctype="multipart/form-data" id="giftForm">
                @csrf

                {{-- Campo oculto para el comportamiento por defecto --}}
                <input type="hidden" name="hide_when_reserved" value="{{ old('hide_when_reserved', 1) }}">

                {{-- EVENTO --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Evento</label>
                    <select name="event_id" class="form-select @error('event_id') is-invalid @enderror" required>
                        <option value="" disabled {{ old('event_id') ? '' : 'selected' }}>Seleccione un evento</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                {{ $event->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NOMBRE --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre del regalo</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           required placeholder="Ej. Cafetera de cápsulas" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CANTIDAD --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad</label>
                    <input type="number" id="quantity" name="quantity"
                           class="form-control cantidad-bloqueada @error('quantity') is-invalid @enderror"
                           value="{{ old('quantity', 1) }}" min="1" readonly>
                    <div class="form-text text-muted">
                        Este campo se desbloqueará si se permiten reservas.
                    </div>
                    @error('quantity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CHECKS --}}
                <div class="mb-3 d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1"
                               {{ old('is_required') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_required">
                            Obligatorio (aplicará para todos los invitados)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="allow_partial" name="allow_partial" value="1"
                               {{ old('allow_partial') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="allow_partial">
                            Permitir reservas (se ocultará cuando alguien lo tome)
                        </label>
                    </div>
                </div>

                {{-- IMAGEN --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Imagen del regalo</label>

                    {{-- Contenedor preview + botón eliminar --}}
                    <div class="position-relative mb-2" style="display:inline-block;">
                        <img id="previewImage" 
                             src="{{ asset('images/no-image.png') }}"
                             alt="Previsualización" 
                             class="img-thumbnail" 
                             style="width:120px;height:120px;object-fit:cover;display:none;">
                        <button type="button" id="removeImageBtn" 
                                class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                                style="display:none;border-radius:50%;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    <div class="form-text">Si no seleccionas una imagen, se usará una por defecto.</div>
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
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
    input[readonly].cantidad-bloqueada {
        background-color: #e9ecef !important;
        cursor: not-allowed;
        color: #6c757d;
        opacity: 1;
    }

    h4 {
        letter-spacing: 0.3px;
    }

    #previewImage {
        border: 1px solid #dee2e6;
    }

    #removeImageBtn {
        transform: translate(30%, -30%);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    #removeImageBtn:hover {
        background-color: #bb2d3b !important;
    }
</style>

{{-- SCRIPT DE LÓGICA --}}
<script>
document.addEventListener("DOMContentLoaded", () => {
    const isRequired = document.getElementById("is_required");
    const allowPartial = document.getElementById("allow_partial");
    const quantity = document.getElementById("quantity");
    const form = document.getElementById("giftForm");
    const imageInput = document.getElementById('image');
    const previewImage = document.getElementById('previewImage');
    const removeImageBtn = document.getElementById('removeImageBtn');

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

    // Previsualizar imagen
    imageInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            previewImage.style.display = 'none';
            removeImageBtn.style.display = 'none';
            previewImage.src = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'inline-block';
            removeImageBtn.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    });

    // Eliminar imagen (también en el servidor si aplica)
    removeImageBtn.addEventListener('click', async () => {
        if (confirm('¿Deseas eliminar esta imagen?')) {
            previewImage.src = '';
            previewImage.style.display = 'none';
            removeImageBtn.style.display = 'none';
            imageInput.value = '';

            // Si estás en modo edición, envía una petición al backend
            const giftId = "{{ isset($gift) ? $gift->id : '' }}";
            if (giftId) {
                try {
                    await fetch(`/gifts/delete-image/${giftId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                } catch (err) {
                    console.error('Error eliminando imagen del servidor:', err);
                }
            }
        }
    });

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
