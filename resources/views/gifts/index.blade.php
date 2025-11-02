@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold text-dark mb-0 text-center flex-grow-1">🎁 Lista de Regalos</h2>
        <a href="{{ route('gifts.create') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Agregar Regalo
        </a>
    </div>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Sin registros --}}
    @if($gifts->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-gift fs-1 d-block mb-2"></i>
            <p class="fs-5">No hay regalos registrados todavía.</p>
        </div>
    @else
    <div class="card shadow-sm border-0">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="tablaRegalos" class="table table-hover align-middle nowrap mb-0" style="width:100%;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Imagen</th>
                            <th>Regalo</th>
                            <th>Evento</th>
                            <th>Cantidad</th>
                            <th>Reservados</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gifts as $gift)
                            @if(!$gift->hide_when_reserved || !method_exists($gift, 'isFullyReserved') || !$gift->isFullyReserved())
                            <tr>
                                <td>{{ $gift->id }}</td>
                                <td style="width: 90px;">
                                    @php
                                        $imagePath = $gift->image_path;
                                        // Si ya es una URL completa (empieza con http o https), úsala tal cual.
                                        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
                                            $imageUrl = $imagePath;
                                        } else {
                                            // Si es una ruta relativa (almacenada en storage), construimos la URL correctamente.
                                            $imageUrl = $imagePath ? asset('storage/'.$imagePath) : asset('images/no-image.png');
                                        }
                                    @endphp

                                    <img src="{{ $imageUrl }}"
                                        alt="{{ $gift->name }}"
                                        class="img-fluid rounded shadow-sm"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td class="fw-semibold">{{ $gift->name }}</td>
                                <td>{{ $gift->event->title ?? 'Sin evento' }}</td>
                                <td>{{ $gift->quantity }}</td>
                                <td>{{ $gift->reserved_count }}</td>
                                <td>
                                    @if($gift->is_required)
                                        <span class="badge bg-primary">Obligatorio</span>
                                    @elseif(method_exists($gift, 'isFullyReserved') && $gift->isFullyReserved())
                                        <span class="badge bg-danger">Agotado</span>
                                    @elseif($gift->is_reserved)
                                        <span class="badge bg-warning text-dark">Parcial</span>
                                    @else
                                        <span class="badge bg-success">Disponible</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('gifts.edit', $gift) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('gifts.destroy', $gift) }}" method="POST" 
                                              onsubmit="return confirm('¿Eliminar este regalo?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- 🔹 Script DataTables Responsive --}}
@push('scripts')
<script>
$(document).ready(function() {
    $('#tablaRegalos').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
        language: {
            search: "🔍 Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ regalos",
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
            { responsivePriority: 1, targets: 2 }, // Regalo
            { responsivePriority: 2, targets: -1 } // Acciones
        ],
        pageLength: 10,
        order: [[0, 'asc']]
    });
});
</script>
@endpush

{{-- CSS adicional para vista responsiva --}}
<style>
.dataTables_wrapper .dataTables_filter input {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 4px 8px;
}
.table thead th {
    white-space: nowrap;
}
.table td {
    vertical-align: middle;
}
</style>
@endsection
