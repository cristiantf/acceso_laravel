@extends('base')

@section('title')Gestión de Asistencia@endsection

@section('sidebar')
<div class="py-3 h-100 d-flex flex-column">
    <!-- Encabezado de la Barra Lateral -->
    <div class="text-center mb-4 pt-3">
        @if(isset($branding) && $branding->logo_path)
            <img src="{{ asset('storage/company-logos/' . $branding->logo_path) }}" alt="Logo" class="img-fluid rounded bg-white p-2 mb-2 shadow-sm" style="max-height: 70px;">
        @else
            <div class="bg-primary d-inline-block p-2 rounded-4 mb-2 shadow">
                <i class="bi bi-shield-lock-fill fs-2 text-white"></i>
            </div>
        @endif
        <h6 class="text-white fw-bold mt-2 small">{{ isset($branding) && isset($branding->textos['nombre_sistema']) ? strtoupper($branding->textos['nombre_sistema']) : 'SISTEMA ISTAE' }}</h6>
    </div>
    
    <!-- Menú de Navegación -->
    <div class="nav flex-column flex-grow-1">
        <div class="section-label">General</div>
        <a href="{{ route('admin_dashboard') }}" class="nav-link"><i class="bi bi-grid-fill me-3"></i> Dashboard</a>
        <a href="{{ route('gestion_asistencia') }}" class="nav-link active"><i class="bi bi-calendar-range-fill me-3"></i> Gestión de Asistencia</a>
        
        <div class="section-label">Gestión Docente</div>
        <a href="{{ route('admin_dashboard') }}#collapseNuevo" class="nav-link">...</a>
        <!-- Resto del menú -->
    </div>

    <!-- Botón de Salida Permanente -->
    <div class="mt-auto p-3">
        <hr class="text-white-50">
        <a href="{{ route('logout') }}" class="btn btn-outline-danger w-100 rounded-pill py-2 small fw-bold">
            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-range-fill me-2 text-primary"></i>Gestión de Registros de Asistencia</h5>
        </div>
        <div class="card-body">
            <!-- Formulario de Filtro -->
            <form method="GET" action="{{ route('gestion_asistencia') }}" class="mb-4 p-3 bg-light rounded-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label small fw-bold">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="{{ $filtros->fecha_inicio }}">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label small fw-bold">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" value="{{ $filtros->fecha_fin }}">
                    </div>
                    <div class="col-md-4">
                        <label for="docente_id" class="form-label small fw-bold">Docente</label>
                        <select name="docente_id" class="form-select">
                            <option value="todos">Todos</option>
                            @foreach($docentes as $d)
                                <option value="{{ $d->id }}" @if($filtros->docente_id == $d->id)selected @endif>{{ $d->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4">Buscar Registros</button>
                        <a href="{{ route('gestion_asistencia') }}" class="btn btn-secondary rounded-pill px-4">Limpiar Filtros</a>
                    </div>
                </div>
            </form>

            <!-- Tabla de Registros -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr class="small text-muted text-uppercase">
                            <th class="ps-4">Docente</th>
                            <th>Fecha y Hora</th>
                            <th>Evento</th>
                            <th>Origen</th>
                            <th>Evidencia</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs_data as $log)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $log->user ? $log->user->nombre : 'Desconocido' }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->fecha)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->tipo_evento }}</td>
                            <td>{{ $log->origen }}</td>
                            <td>
                                @if($log->origen == 'Asistencia remota' && ($log->latitud || $log->foto_path || $log->descripcion))
                                    <button class="btn btn-sm btn-outline-primary border-0" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#evidenceDetailModal"
                                            data-user="{{ $log->user ? $log->user->nombre : 'N/A' }}"
                                            data-date="{{ \Carbon\Carbon::parse($log->fecha)->format('Y-m-d H:i:s') }}"
                                            data-desc="{{ $log->descripcion ?? '' }}"
                                            data-lat="{{ $log->latitud ?? '' }}"
                                            data-lon="{{ $log->longitud ?? '' }}"
                                            data-foto="{{ $log->foto_path ? asset('storage/uploads/' . $log->foto_path) : '' }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('editar_asistencia', ['id' => $log->id]) }}" class="btn btn-sm btn-outline-primary border-0" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                <a href="{{ route('eliminar_asistencia', ['id' => $log->id]) }}" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Está seguro de eliminar este registro? La acción no se puede deshacer.')" title="Eliminar"><i class="bi bi-trash-fill"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-2 d-block mb-2"></i>
                                No se encontraron registros con los filtros actuales.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('includes/evidence_modal') {{-- Incluir el modal de evidencia --}}
@endsection
