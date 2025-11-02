@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- 🏷️ Título centrado --}}
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Invitados 🎉</h1>
        <p class="text-gray-600 text-sm mt-1">Administra, importa y gestiona los invitados de tus eventos.</p>
    </div>

    {{-- 🎛️ Card: Importar Excel y Acciones --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form id="formImportExcel" action="{{ route('importarExcel') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf

                {{-- 🟢 Selección de evento --}}
                <div class="col-md-4">
                    <label for="event_id" class="form-label fw-semibold">Seleccionar evento</label>
                    <select name="event_id" id="event_id" class="form-select" required>
                        <option value="">-- Selecciona un evento --</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}">{{ $event->title ?? $event->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 🧾 Archivo Excel --}}
                <div class="col-md-4">
                    <label for="excelFile" class="form-label fw-semibold">Archivo Excel (.xlsx)</label>
                    <input type="file" name="excelFile" id="excelFile" accept=".xlsx,.xls,.csv" class="form-control" required>
                </div>

                {{-- ⚙️ Botones de acción --}}
                <div class="col-md-4 d-flex flex-wrap gap-2 justify-content-md-end">
                    <a href="{{ asset('templates/guests_template.xlsx') }}" class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-download"></i> Descargar formato
                    </a>

                    <a href="{{ route('guests.create') }}" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-person-plus"></i> Nuevo invitado
                    </a>

                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="bi bi-upload"></i> Importar Excel
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ✅ Mensajes de estado --}}
    @if (session('success'))
        <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-100 text-red-800 px-4 py-2 rounded">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- 🧾 Tabla de invitados con DataTables responsive --}}
    @if($guests->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaInvitados" class="table table-striped table-hover align-middle nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Evento</th>
                            <th>Acompañantes</th>
                            <th class="text-center"># Acompañantes</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guests as $guest)
                            <tr>
                                <td>{{ $guest->id }}</td>
                                <td class="fw-semibold">{{ $guest->name }}</td>
                                <td>{{ $guest->email ?? '—' }}</td>
                                <td>{{ $guest->phone ?? '—' }}</td>
                                <td>{{ $guest->event->title ?? 'Sin evento' }}</td>
                                <td>
                                    @if($guest->companions_names)
                                        {{ str_replace('|', ', ', $guest->companions_names) }}
                                    @else
                                        <span class="text-muted fst-italic">Sin acompañantes</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $guest->companions_count ?? 0 }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('guests.edit', $guest) }}" class="btn btn-sm btn-warning text-white">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('guests.destroy', $guest) }}" method="POST" class="formEliminar d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger btnEliminar" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalConfirmarEliminar"
                                                    data-nombre="{{ $guest->name }}"
                                                    data-action="{{ route('guests.destroy', $guest) }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="bg-warning-subtle border border-warning text-warning-emphasis px-4 py-5 rounded-lg text-center shadow-sm mt-4">
        <p class="fs-5 fw-bold">😅 No hay invitados registrados.</p>
        <p>Puedes agregar uno manualmente o importar un archivo Excel.</p>
    </div>
    @endif

</div>

<!-- Modal de Progreso -->
<div class="modal fade" id="modalCargando" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-3">
      <h5 class="fw-bold mb-3">Procesando archivo...</h5>
      <div class="progress" style="height: 25px;">
        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
             role="progressbar" style="width: 0%">0%</div>
      </div>
      <div id="resultadoImport" class="mt-3"></div>
    </div>
  </div>
</div>

<!-- Modal resultado -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4 text-center">
      <h5 class="fw-bold">Importación completada ✅</h5>
      <p id="resultMessage" class="mb-0 mt-2"></p>
      <button class="btn btn-success mt-3" data-bs-dismiss="modal">Aceptar</button>
    </div>
  </div>
</div>

<!-- Modal de error -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4 text-center">
      <h5 class="fw-bold text-danger">❌ Error al importar</h5>
      <p id="errorMessage" class="mb-0 mt-2 text-danger"></p>
      <button class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cerrar</button>
    </div>
  </div>
</div>

<!-- 🗑️ Modal de confirmación de eliminación -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalEliminarLabel">
          <i class="bi bi-exclamation-triangle"></i> Confirmar eliminación
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body text-center">
        <p class="fs-5">¿Seguro que deseas eliminar al invitado <strong id="nombreInvitado"></strong>?</p>
        <p class="text-muted mb-0">Esta acción no se puede deshacer.</p>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form id="formConfirmarEliminar" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-trash"></i> Eliminar
            </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- 🔹 Script DataTables Responsive --}}
@push('scripts')
<script>
$(document).ready(function() {
    $('#tablaInvitados').DataTable({
        responsive: true,
        language: {
            search: "🔍 Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ invitados",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            zeroRecords: "No se encontraron coincidencias",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // Nombre
            { responsivePriority: 2, targets: -1 } // Acciones
        ],
        pageLength: 10,
        order: [[0, 'asc']]
    });
});
</script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalConfirmarEliminar');
    const nombreSpan = document.getElementById('nombreInvitado');
    const form = document.getElementById('formConfirmarEliminar');

    // Cuando se abre el modal, actualiza el nombre y acción
    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const nombre = button.getAttribute('data-nombre');
        const action = button.getAttribute('data-action');

        nombreSpan.textContent = nombre;
        form.setAttribute('action', action);
    });
});
</script>

<script>
document.getElementById('formImportExcel').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const modal = new bootstrap.Modal(document.getElementById('modalCargando'));
    const progressBar = document.getElementById('progressBar');
    const resultMessage = document.getElementById('resultadoImport');

    // Reiniciar estado
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
    resultMessage.textContent = '';
    modal.show();

    // Simulación de progreso
    let progress = 0;
    const interval = setInterval(() => {
        if (progress < 90) {
            progress += 10;
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
        } else {
            clearInterval(interval);
        }
    }, 300);

    fetch("{{ route('importarExcel') }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(interval);
        progressBar.style.width = '100%';
        progressBar.textContent = '100%';

        if (data.success) {
            resultMessage.innerHTML = `
                <div class="alert alert-success mt-3 text-start">
                    <strong>${data.message}</strong><br>
                    Total procesados: ${data.total}<br>
                    Insertados: ${data.insertados}<br>
                    Con error: ${data.errores}
                </div>
            `;
        } else {
            resultMessage.innerHTML = `
                <div class="alert alert-danger mt-3">${data.message}</div>
            `;
        }

        // Cerrar modal después de unos segundos
        setTimeout(() => {
            modal.hide();
            location.reload(); // Refresca automáticamente la página
        }, 2500);
    })
    .catch(error => {
        clearInterval(interval);
        resultMessage.innerHTML = `
            <div class="alert alert-danger mt-3">
                ❌ Error inesperado: ${error.message}
            </div>
        `;
    });
});
</script>
@endsection