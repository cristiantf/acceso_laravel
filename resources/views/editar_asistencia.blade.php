@extends('base')

@section('title')Editar Asistencia@endsection

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin_dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('gestion_asistencia') }}" class="text-decoration-none">Gestión Asistencia</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Registro</li>
                </ol>
            </nav>

            <!-- Tarjeta principal -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-fill me-2"></i>Editar Registro de Asistencia</h5>
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

                    <!-- Información del registro original -->
                    <div class="alert alert-info mb-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Registro Original:</strong> 
                            {{ \Carbon\Carbon::parse($log->fecha)->format('d/m/Y H:i:s') }} - 
                            {{ $log_user ? $log_user->nombre : 'ID: ' . $log->usuario_id }}
                        </small>
                    </div>

                    <form action="{{ route('actualizar_asistencia') }}" method="POST">
                        @csrf
                        <input type="hidden" name="log_id" value="{{ $log->id }}">
                        
                        <div class="row g-3">
                            <!-- Docente -->
                            <div class="col-md-6">
                                <label for="docente_id" class="form-label fw-bold">
                                    <i class="bi bi-person-fill text-primary me-2"></i>Docente
                                </label>
                                <select name="docente_id" id="docente_id" class="form-select @error('docente_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Docente --</option>
                                    @foreach($docentes as $d)
                                        <option value="{{ $d->id }}" 
                                            @if(isset($log_user) && $log_user->id == $d->id) selected @endif
                                            @if(old('docente_id') == $d->id) selected @endif>
                                            {{ $d->nombre }} (ID: {{ $d->biometric_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha y Hora -->
                            <div class="col-md-6">
                                <label for="fecha" class="form-label fw-bold">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>Fecha y Hora
                                </label>
                                <input type="datetime-local" 
                                    class="form-control @error('fecha') is-invalid @enderror" 
                                    name="fecha" id="fecha" 
                                    value="{{ old('fecha', \Carbon\Carbon::parse($log->fecha)->format('Y-m-d\TH:i')) }}" 
                                    required>
                                @error('fecha')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo de Evento -->
                            <div class="col-md-6">
                                <label for="tipo_evento" class="form-label fw-bold">
                                    <i class="bi bi-tag text-primary me-2"></i>Tipo de Evento
                                </label>
                                <select name="tipo_evento" id="tipo_evento" class="form-select @error('tipo_evento') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Tipo --</option>
                                    <option value="ENTRADA" @if(old('tipo_evento', $log->tipo_evento) == 'ENTRADA') selected @endif>Entrada</option>
                                    <option value="SALIDA" @if(old('tipo_evento', $log->tipo_evento) == 'SALIDA') selected @endif>Salida</option>
                                    <option value="ASISTENCIA_WEB" @if(old('tipo_evento', $log->tipo_evento) == 'ASISTENCIA_WEB') selected @endif>Asistencia Web</option>
                                    <option value="APERTURA_REMOTA" @if(old('tipo_evento', $log->tipo_evento) == 'APERTURA_REMOTA') selected @endif>Apertura Remota</option>
                                </select>
                                @error('tipo_evento')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Origen -->
                            <div class="col-md-6">
                                <label for="origen" class="form-label fw-bold">
                                    <i class="bi bi-map text-primary me-2"></i>Origen
                                </label>
                                <select name="origen" id="origen" class="form-select @error('origen') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Origen --</option>
                                    <option value="dispositivo" @if(old('origen', $log->origen) == 'dispositivo') selected @endif>Dispositivo</option>
                                    <option value="web" @if(old('origen', $log->origen) == 'web') selected @endif>Web</option>
                                    <option value="manual" @if(old('origen', $log->origen) == 'manual') selected @endif>Manual</option>
                                </select>
                                @error('origen')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-bold">
                                    <i class="bi bi-chat-left-text text-primary me-2"></i>Descripción (Opcional)
                                </label>
                                <textarea name="descripcion" id="descripcion" 
                                    class="form-control @error('descripcion') is-invalid @enderror" 
                                    rows="3" maxlength="500"
                                    placeholder="Notas adicionales del registro...">{{ old('descripcion', $log->descripcion ?? '') }}</textarea>
                                <small class="text-muted d-block mt-1">
                                    <span id="char_count">0</span>/500 caracteres
                                </small>
                                @error('descripcion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Información de Geolocalización (si existe) -->
                            @if($log->latitud && $log->longitud)
                            <div class="col-12">
                                <div class="alert alert-secondary mb-0">
                                    <h6 class="alert-heading mb-2">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>Datos de Geolocalización
                                    </h6>
                                    <small>
                                        <strong>Latitud:</strong> {{ $log->latitud }}<br>
                                        <strong>Longitud:</strong> {{ $log->longitud }}<br>
                                        <strong>Precisión:</strong> {{ $log->precision ?? 'No disponible' }}m
                                    </small>
                                </div>
                            </div>
                            @endif

                            <!-- Evidencia Fotográfica -->
                            @if($log->foto_path)
                            <div class="col-12">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-image text-primary me-2"></i>Evidencia Fotográfica
                                </label>
                                <div class="text-center">
                                    <img src="{{ asset('storage/' . $log->foto_path) }}" 
                                        class="img-fluid rounded-3 shadow-sm" 
                                        style="max-height: 300px; object-fit: cover;"
                                        alt="Evidencia fotográfica">
                                </div>
                            </div>
                            @endif

                            <!-- Información Adicional -->
                            <div class="col-12">
                                <div class="alert alert-light border">
                                    <small class="text-muted">
                                        <strong>Creado:</strong> {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}<br>
                                        @if($log->updated_at)
                                        <strong>Última actualización:</strong> {{ \Carbon\Carbon::parse($log->updated_at)->format('d/m/Y H:i:s') }}<br>
                                        @endif
                                        <strong>ID Registro:</strong> #{{ $log->id }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="{{ route('gestion_asistencia') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Volver
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('eliminar_asistencia', $log->id) }}" 
                                    class="btn btn-outline-danger rounded-pill px-4 me-2"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este registro?')">
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
        </div>
    </div>
</div>

<!-- Script para contador de caracteres -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const descripcion = document.getElementById('descripcion');
    const charCount = document.getElementById('char_count');
    
    function updateCount() {
        charCount.textContent = descripcion.value.length;
    }
    
    descripcion.addEventListener('input', updateCount);
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
</style>
@endsection
