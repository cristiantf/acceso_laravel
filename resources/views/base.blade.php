<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ isset($branding) && isset($branding->textos['nombre_sistema']) ? $branding->textos['nombre_sistema'] : 'Sistema ISTAE' }}</title>
    <!-- Bootstrap 5 y Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('static/css/style.css') }}">
    <style>
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-color: #0d6efd;
        }

        body { 
            background-color: #f4f7f6; 
            min-height: 100vh;
            padding-top: var(--navbar-height);
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Sidebar Styling */
        .sidebar { 
            width: var(--sidebar-width); 
            position: fixed; 
            top: 0; 
            left: 0; 
            height: 100vh; 
            background: #1a1d20; 
            color: #fff; 
            transition: all 0.3s ease;
            z-index: 1050; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        /* Navbar Styling */
        .navbar-top {
            height: var(--navbar-height);
            z-index: 1040;
            left: var(--sidebar-width); 
            transition: all 0.3s ease;
            background-color: #212529 !important;
        }

        .main-content { 
            margin-left: var(--sidebar-width); 
            transition: all 0.3s ease;
            padding: 25px;
        }

        /* Estado Contraído (Escritorio) */
        body.sidebar-collapsed .sidebar {
            left: calc(-1 * var(--sidebar-width));
        }
        body.sidebar-collapsed .navbar-top {
            left: 0 !important;
        }
        body.sidebar-collapsed .main-content {
            margin-left: 0;
        }
        
        /* Ajustes para Móvil */
        @media (max-width: 991.98px) {
            .sidebar { left: calc(-1 * var(--sidebar-width)); }
            .sidebar.active { left: 0; }
            .navbar-top { left: 0 !important; }
            .main-content { margin-left: 0; }
            #sidebar-toggler { display: block; }
        }

        .nav-link {
            border-radius: 8px;
            margin: 4px 12px;
            padding: 10px 15px;
            transition: all 0.2s;
            color: rgba(255,255,255,0.8) !important;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff !important;
        }
        .nav-link.active {
            background-color: var(--primary-color) !important;
            color: #fff !important;
        }

        .section-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #6c757d; /* Gris claro sobre fondo oscuro */
            padding: 20px 25px 5px;
            font-weight: 800;
            letter-spacing: 1.2px;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 1045;
            top: 0;
            left: 0;
        }
        .sidebar-overlay.active { display: block; }
    </style>

    {{-- INICIO: Configuración de Branding Multi-Tenant --}}
    @if(isset($branding))
    <style>
        :root {
            /* Variables de Bootstrap personalizadas */
            --bs-primary: {{ $branding->colores['primario'] ?? '#0d6efd' }};
            --bs-secondary: {{ $branding->colores['secundario'] ?? '#6c757d' }};
            --bs-success: {{ $branding->colores['acento'] ?? '#198754' }};
            --sidebar-bg: {{ $branding->colores['barra_lateral'] ?? '#1a1d20' }};
            --primary-color: {{ $branding->colores['primario'] ?? '#0d6efd' }};
        }

        /* Forzar los colores de botones y textos principales */
        .text-primary { color: var(--bs-primary) !important; }
        .bg-primary { background-color: var(--bs-primary) !important; }
        
        .btn-primary { 
            background-color: var(--bs-primary) !important; 
            border-color: var(--bs-primary) !important; 
            color: #fff !important;
        }
        .btn-outline-primary {
            color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--bs-primary) !important;
            color: white !important;
        }

        /* Ajustar el fondo del menú lateral y barra superior */
        .sidebar { background-color: var(--sidebar-bg) !important; }
        .navbar-top { background-color: {{ $branding->colores['barra_navegacion'] ?? '#212529' }} !important; }
        
        @if(($branding->tema ?? 'light') == 'dark')
            body { background-color: #121212 !important; color: #e0e0e0 !important; }
            .card, .bg-white { background-color: #1e1e1e !important; border-color: #333 !important; color: #e0e0e0 !important; }
            .card-header, .bg-light, .table { background-color: #2c2c2c !important; color: #e0e0e0 !important; border-color: #444 !important; }
        @endif
    </style>
    @endif
    {{-- FIN: Configuración de Branding Multi-Tenant --}}
</head>
<body class="@yield('body_class')">
    <div class="sidebar-overlay" id="overlay"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            @yield('sidebar')
        </nav>

        <div id="page-content-wrapper" class="w-100">
            <!-- Navbar -->
            <nav class="navbar navbar-dark fixed-top shadow-sm navbar-top">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-light border-0 me-2" id="sidebar-toggler">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                        <a class="navbar-brand fw-bold" href="#">🎓 {{ isset($branding) && isset($branding->textos['nombre_sistema']) ? strtoupper($branding->textos['nombre_sistema']) : 'ISTAE ACCESO' }}</a>
                        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                            @if(isset($branding) && $branding->logo_path)
                                <img src="{{ asset('storage/company-logos/' . $branding->logo_path) }}" alt="Logo" style="max-height: 40px; object-fit: contain;" class="me-2 rounded bg-white p-1">
                            @else
                                🎓 {{ isset($branding) && isset($branding->textos['nombre_sistema']) ? strtoupper($branding->textos['nombre_sistema']) : 'ISTAE ACCESO' }}
                            @endif
                        </a>
                    </div>

                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="d-none d-sm-inline me-2">{{ auth()->check() && auth()->user()->nombre ? explode(' ', auth()->user()->nombre)[0] : 'Usuario' }}</span>
                            <i class="bi bi-person-circle fs-4"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item py-2" href="{{ route('perfil') }}"><i class="bi bi-key-fill me-2 text-primary"></i> Seguridad</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="{{ route('logout') }}"><i class="bi bi-box-arrow-right me-2"></i> Salir</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error') || session('danger'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        {{ session('error') ?? session('danger') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
                
                @if(isset($branding))
                <footer class="text-center mt-5 mb-3 text-muted small">
                    {{ $branding->textos['pie_pagina'] ?? '© 2026 ISTAE' }}
                    @if($branding->mostrar_marca_agua ?? false)<br><span class="extra-small opacity-50">Powered by ISTAE Access Control</span>@endif
                </footer>
                @endif
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggler = document.getElementById('sidebar-toggler');
        const overlay = document.getElementById('overlay');
        const body = document.body;

        function toggleSidebar() {
            if (window.innerWidth > 991.98) {
                body.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }

        if(toggler) toggler.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // Lógica para abrir colapsables desde la URL
        window.addEventListener('load', () => {
            const hash = window.location.hash;
            if (hash && hash.includes('collapse')) {
                const el = document.querySelector(hash);
                if (el) {
                    const bs = bootstrap.Collapse.getOrCreateInstance(el);
                    bs.show();
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    </script>
</body>
</html>