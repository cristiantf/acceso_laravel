@extends('base')

@section('title')Editar Permiso@endsection

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin_dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('gestion_permisos') }}" class="text-decoration-none">Gestión Permisos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Permiso</li>
                </ol>
            </nav>

            <!-- Tarjeta Principal -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="bi bi-calendar-check me-2"></i>Actualizar Permiso del Docente
                </div>

                <div class="card-body p-4">
                    <!-- Mostrar errores si existen -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Errores de Validación</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Información del permiso -->
                    <div class="alert alert-info mb-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>ID Permiso:</strong> #{{ $permiso->id }} - 
                            <strong>Creado:</strong> {{ \Carbon\Carbon::parse($permiso->created_at)->format('d/m/Y H:i:s') }}
                        </small>
                    </div>

                    <form action="{{ route('actualizar_permiso') }}" method="POST">
                        @csrf
                        <input type="hidden" name="permiso_id" value="{{ $permiso->id }}">
                        
                        <div class="row g-3">
                            <!-- Docente -->
                            <div class="col-12">
                                <label for="docente_id" class="form-label fw-bold">
                                    <i class="bi bi-person-fill text-primary me-2"></i>Docente
                                </label>
                                <select name="docente_id" id="docente_id" class="form-select @error('docente_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Docente --</option>
                                    @foreach($docentes as $d)
                                    <option value="{{ $d->id }}" 
                                        @if($d->id == old('docente_id', $permiso->user_id)) selected @endif>
                                        {{ $d->nombre }} (ID: {{ $d->biometric_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha del Permiso -->
                            <div class="col-md-6">
                                <label for="fecha_permiso" class="form-label fw-bold">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>Fecha del Permiso
                                </label>
                                <input type="date" 
                                    name="fecha_permiso" 
                                    id="fecha_permiso" 
                                    class="form-control @error('fecha_permiso') is-invalid @enderror" 
                                    value="{{ old('fecha_permiso', \Carbon\Carbon::parse($permiso->fecha_permiso)->format('Y-m-d')) }}" 
                                    required
                                    min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                                @error('fecha_permiso')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo de Permiso -->
                            <div class="col-md-6">
                                <label for="tipo" class="form-label fw-bold">
                                    <i class="bi bi-tag text-primary me-2"></i>Tipo de Permiso
                                </label>
                                <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror">
                                    <option value="">-- Seleccionar Tipo --</option>
                                    <option value="permiso" 
                                        @if(old('tipo', $permiso->tipo ?? 'permiso') == 'permiso') selected @endif>
                                        Permiso (horas)
                                    </option>
                                    <option value="comisión" 
                                        @if(old('tipo', $permiso->tipo ?? '') == 'comisión') selected @endif>
                                        Comisión (fuera de institución)
                                    </option>
                                    <option value="licencia" 
                                        @if(old('tipo', $permiso->tipo ?? '') == 'licencia') selected @endif>
                                        Licencia (1+ días)
                                    </option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Observación -->
                            <div class="col-12">
                                <label for="observacion" class="form-label fw-bold">
                                    <i class="bi bi-chat-left-text text-primary me-2"></i>Observación (Opcional)
                                </label>
                                <textarea name="observacion" 
                                    id="observacion" 
                                    class="form-control @error('observacion') is-invalid @enderror" 
                                    rows="3" 
                                    maxlength="500"
                                    placeholder="Razón o justificación del permiso...">{{ old('observacion', $permiso->observacion ?? '') }}</textarea>
                                <small class="text-muted d-block mt-1">
                                    <span id="char_count">{{ strlen($permiso->observacion ?? '') }}</span>/500 caracteres
                                </small>
                                @error('observacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado actual del permiso -->
                            <div class="col-12">
                                <div class="alert alert-light border">
                                    <h6 class="alert-heading mb-2">
                                        <i class="bi bi-info-circle text-info me-2"></i>Información del Permiso
                                    </h6>
                                    <small class="text-muted">
                                        <strong>Docente:</strong> 
                                        @php
                                            $docente = $permiso->docente ?? \App\Models\User::find($permiso->user_id);
                                        @endphp
                                        {{ $docente ? $docente->nombre : 'No disponible' }}<br>
                                        
                                        <strong>Fecha Permiso:</strong> 
                                        {{ \Carbon\Carbon::parse($permiso->fecha_permiso)->format('d/m/Y (l)') }}<br>
                                        
                                        <strong>Creado hace:</strong> 
                                        {{ \Carbon\Carbon::parse($permiso->created_at)->diffForHumans() }}<br>

                                        @if($permiso->updated_at && $permiso->updated_at != $permiso->created_at)
                                        <strong>Última actualización:</strong> 
                                        {{ \Carbon\Carbon::parse($permiso->updated_at)->diffForHumans() }}<br>
                                        @endif

                                        <strong>Estado:</strong> 
                                        <span class="badge bg-success">Activo</span>
                                    </small>
                                </div>
                            </div>

                            <!-- Advertencias -->
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">
                                    <small class="text-muted">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        <strong>Nota:</strong> Cambiar la fecha a una fecha pasada no es permitido. 
                                        Los permisos con fecha pasada solo pueden ser eliminados.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="{{ route('gestion_permisos') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Volver
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('eliminar_permiso', $permiso->id) }}" 
                                    class="btn btn-outline-danger rounded-pill px-4 me-2"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este permiso?')">
                                    <i class="bi bi-trash me-2"></i>Eliminar
                                </a>
                                <button type="submit" class="btn btn-primary fw-bold rounded-pill px-5">
                                    <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de ayuda -->
            <div class="card mt-4 border-0 bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-2">
                        <i class="bi bi-question-circle text-info me-2"></i>Tipos de Permisos
                    </h6>
                    <small class="text-muted">
                        <strong>Permiso:</strong> Salidas cortas durante el día (horas)<br>
                        <strong>Comisión:</strong> Trabajo fuera de la institución<br>
                        <strong>Licencia:</strong> Ausencia justificada por uno o más días
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para contador de caracteres -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const observacion = document.getElementById('observacion');
    const charCount = document.getElementById('char_count');
    
    function updateCount() {
        charCount.textContent = observacion.value.length;
    }
    
    observacion.addEventListener('input', updateCount);
    updateCount(); // Inicializar
});
</script>

<style>
.form-label {
    color: #212529;
    font-size: 0.95rem;
}

.form-select, .form-control {
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.breadcrumb {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffc107;
}
</style>
@endsection
