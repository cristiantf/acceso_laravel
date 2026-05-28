<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - {{ isset($branding) && isset($branding->textos['nombre_sistema']) ? $branding->textos['nombre_sistema'] : 'Sistema Biométrico ISTAE' }}</title>
    <!-- Bootstrap 5 y Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
        background: linear-gradient(135deg, var(--bg-color, #f0f2f5) 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .login-header {
            background-color: #ffffff;
            padding: 2.5rem 2rem 1rem;
            text-center: center;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            background-color: #fff;
        border-color: var(--primary-color, #0d6efd);
        box-shadow: 0 0 0 0.25rem var(--primary-shadow, rgba(13, 110, 253, 0.15));
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
            color: #6c757d;
        }
        .login-logo {
            font-size: 4rem;
        color: var(--primary-color, #0d6efd);
        filter: drop-shadow(0 4px 6px var(--primary-shadow, rgba(13, 110, 253, 0.2)));
        }
        .btn-login {
            padding: 0.8rem;
            border-radius: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        background-color: var(--primary-color, #0d6efd);
        border-color: var(--primary-color, #0d6efd);
        color: #fff;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        box-shadow: 0 5px 15px var(--primary-shadow, rgba(13, 110, 253, 0.3));
        background-color: var(--primary-color, #0b5ed7);
        border-color: var(--primary-color, #0a58ca);
        color: #fff;
        }
        /* Ajuste de alertas */
        .alert {
            border-radius: 0.75rem;
            border: none;
        }
    </style>

    @if(isset($branding))
    <style>
        :root {
            --primary-color: {{ $branding->colores['primario'] ?? '#0d6efd' }};
            --primary-shadow: {{ $branding->colores['primario'] ?? '#0d6efd' }}40;
            --bg-color: {{ $branding->colores['fondo_login'] ?? '#f0f2f5' }};
        }
        @if(($branding->tema ?? 'light') == 'dark')
            body { background: #121212 !important; }
            .card-login { background-color: #1e1e1e !important; color: #e0e0e0 !important; }
            .login-header, .card-footer { background-color: #1e1e1e !important; }
            .form-control, .input-group-text { background-color: #2c2c2c !important; border-color: #444 !important; color: #e0e0e0 !important; }
            .text-dark { color: #fff !important; }
        @endif
    </style>
    @endif
</head>
<body>
    <div class="container p-3">
        <div class="row justify-content-center">
            <div class="col-12 d-flex justify-content-center">
                <div class="card card-login shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <!-- Logo y Encabezado -->
                        <div class="text-center mb-4">
                            @if(isset($branding) && $branding->logo_path)
                                <img src="{{ asset('storage/company-logos/' . $branding->logo_path) }}" alt="Logo" class="img-fluid mb-3 shadow-sm rounded bg-white p-2" style="max-height: 80px;">
                            @else
                                <i class="bi bi-fingerprint login-logo"></i>
                            @endif
                            <h3 class="fw-bold mt-3 text-dark">{{ isset($branding) && isset($branding->textos['nombre_sistema']) ? $branding->textos['nombre_sistema'] : 'Sistema ISTAE' }}</h3>
                            <p class="text-muted small">{{ isset($branding) && isset($branding->textos['subtitulo']) ? $branding->textos['subtitulo'] : 'Control de Asistencia Biométrico' }}</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                                    <span class="small">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Formulario -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nombre de Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control shadow-none" 
                                           placeholder="Nombre de usuario" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control shadow-none" 
                                           placeholder="••••••••" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-2"></i>INGRESAR AL PANEL
                            </button>
                        </form>
                    </div>
                    
                    <!-- Footer del Card -->
                    <div class="card-footer text-muted text-center small py-3 bg-white border-0">
                        <div class="px-3">
                            <div class="mb-1">¿Olvidó sus credenciales?</div>
                        <span class="fw-bold text-dark">{{ isset($branding) && isset($branding->textos['nombre_empresa']) ? $branding->textos['nombre_empresa'] : 'Instituto Superior Tecnológico Alberto Enríquez' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>