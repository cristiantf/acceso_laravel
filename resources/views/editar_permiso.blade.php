@extends('base')

@section('body_class') no-sidebar @endsection

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <!-- Navegación -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin_dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Permiso</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="bi bi-calendar-check me-2"></i>Actualizar Permiso del Docente
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('actualizar_permiso') }}" method="POST">
                        @csrf
                        <input type="hidden" name="permiso_id" value="{{ $permiso->id }}">
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Docente</label>
                                <select name="docente_id" class="form-select" required>
                                    @foreach($docentes as $d)
                                    <option value="{{ $d->id }}" @if($d->id == $permiso->user_id)selected @endif>{{ $d->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Fecha del Permiso</label>
                                <input type="date" name="fecha_permiso" class="form-control" value="{{ \Carbon\Carbon::parse($permiso->fecha_permiso)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Observación (Opcional)</label>
                                <textarea name="observacion" class="form-control" rows="3">{{ $permiso->observacion ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary fw-bold py-2">
                                <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                            </button>
                            <a href="{{ route('admin_dashboard') }}" class="btn btn-outline-secondary py-2">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

