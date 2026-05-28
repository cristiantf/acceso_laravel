@extends('base')

@section('title') Configuración de Marca @endsection

@section('sidebar')
    {{-- Puedes incluir aquí tu menú lateral estándar si lo prefieres, o extender de un layout que ya lo tenga --}}
    <div class="p-3">
        <a href="{{ route('admin_dashboard') }}" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-arrow-left me-2"></i>Volver al Dashboard</a>
        <hr>
        <h6 class="text-muted small fw-bold">CONFIGURACIÓN SAAS</h6>
        <div class="nav flex-column">
            <a href="#" class="nav-link active fw-bold text-primary"><i class="bi bi-palette-fill me-2"></i> Branding y Colores</a>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-palette-fill me-2 text-primary"></i>
                Configuración de Marca ({{ $company->nombre }})
            </h5>
        </div>
        <div class="card-body p-4 p-md-5">
            
            <!-- SECCIÓN: Logo -->
            <div class="mb-5 bg-light p-4 rounded-4">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-image me-2"></i>Logotipo de la Empresa</h6>
                <form action="{{ route('admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0">
                            @if($branding->logo_path)
                                <img src="{{ asset('storage/company-logos/' . $branding->logo_path) }}" 
                                     class="img-fluid rounded-3 bg-white p-2 shadow-sm" style="max-height: 80px;">
                            @else
                                <div class="bg-white rounded-3 p-4 text-center shadow-sm">
                                    <i class="bi bi-image fs-1 text-muted opacity-50"></i>
                                    <p class="text-muted extra-small mt-2 mb-0">Sin logo</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">Subir Logo</button>
                            </div>
                            <small class="text-muted mt-2 d-block">Recomendado: Imagen PNG transparente (200x60px). Máx 5MB.</small>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCIÓN: Formulario principal de colores y textos -->
            <form action="{{ route('admin.branding.update') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Columna Izquierda: Colores -->
                    <div class="col-lg-6 mb-4">
                        <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-droplet-fill me-2"></i>Paleta de Colores</h6>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-muted">Color Primario</label>
                                <div class="input-group">
                                    <input type="color" name="colores[primario]" class="form-control form-control-color border-end-0 p-1" value="{{ $branding->colores['primario'] ?? '#0d6efd' }}">
                                    <input type="text" class="form-control font-monospace" value="{{ $branding->colores['primario'] ?? '#0d6efd' }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-muted">Color Secundario</label>
                                <div class="input-group">
                                    <input type="color" name="colores[secundario]" class="form-control form-control-color border-end-0 p-1" value="{{ $branding->colores['secundario'] ?? '#6c757d' }}">
                                    <input type="text" class="form-control font-monospace" value="{{ $branding->colores['secundario'] ?? '#6c757d' }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-muted">Color de Acento</label>
                                <div class="input-group">
                                    <input type="color" name="colores[acento]" class="form-control form-control-color border-end-0 p-1" value="{{ $branding->colores['acento'] ?? '#198754' }}">
                                    <input type="text" class="form-control font-monospace" value="{{ $branding->colores['acento'] ?? '#198754' }}" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-muted">Barra Lateral</label>
                                <div class="input-group">
                                    <input type="color" name="colores[barra_lateral]" class="form-control form-control-color border-end-0 p-1" value="{{ $branding->colores['barra_lateral'] ?? '#1a1d20' }}">
                                    <input type="text" class="form-control font-monospace" value="{{ $branding->colores['barra_lateral'] ?? '#1a1d20' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 mt-5 text-primary"><i class="bi bi-moon-stars-fill me-2"></i>Tema Base</h6>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline border rounded p-3 bg-light w-100 m-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="tema" id="tema_light" value="light" @if(($branding->tema ?? 'light') == 'light') checked @endif>
                                <label class="form-check-label fw-bold mt-1" for="tema_light">☀️ Claro</label>
                            </div>
                            <div class="form-check form-check-inline border rounded p-3 bg-dark text-white w-100 m-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="tema" id="tema_dark" value="dark" @if(($branding->tema ?? 'light') == 'dark') checked @endif>
                                <label class="form-check-label fw-bold mt-1" for="tema_dark">🌙 Oscuro</label>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Textos -->
                    <div class="col-lg-6 mb-4 border-start ps-lg-4">
                        <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-type me-2"></i>Textos Personalizados</h6>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Nombre del Sistema</label>
                                <input type="text" name="nombre_sistema" class="form-control" value="{{ $branding->textos['nombre_sistema'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Subtítulo (Aparece en login)</label>
                                <input type="text" name="subtitulo" class="form-control" value="{{ $branding->textos['subtitulo'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Pie de Página (Copyright)</label>
                                <textarea name="pie_pagina" class="form-control" rows="2">{{ $branding->textos['pie_pagina'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light border rounded-3">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                                <label class="form-check-label fw-bold mb-0" for="marca_agua">Mostrar marca de agua de "Powered by ISTAE"</label>
                                <input class="form-check-input ms-0" type="checkbox" name="mostrar_marca_agua" id="marca_agua" value="1" style="transform: scale(1.3);" @if($branding->mostrar_marca_agua ?? false) checked @endif>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4">Restaurar Valores por Defecto</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Guardar Cambios de Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control-color { width: 50px; cursor: pointer; }
    .font-monospace { font-size: 0.85rem; }
</style>
@endsection