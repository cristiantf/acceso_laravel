# 🚀 GUÍA PRÁCTICA DE IMPLEMENTACIÓN - Ejemplos de Código

## 1️⃣ STEP 1: Crear Tabla Companies

```bash
php artisan make:migration create_companies_table
```

**Archivo generado:** `database/migrations/XXXX_create_companies_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('nombre', 150)->unique();
            $table->string('email', 150)->unique();
            $table->text('descripcion')->nullable();
            
            // Subdomain para acceso
            $table->string('subdomain', 100)->unique();
            
            // Plan y suscripción
            $table->enum('plan_tipo', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->date('fecha_inicio_suscripcion');
            $table->date('fecha_fin_suscripcion');
            $table->boolean('activa')->default(true);
            
            // Límites
            $table->integer('limite_usuarios')->default(20);
            
            // Configuración
            $table->string('zona_horaria', 50)->default('America/Guayaquil');
            $table->string('idioma', 5)->default('es');
            $table->json('caracteristicas_habilitadas')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('activa');
            $table->index('plan_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
```

---

## 2️⃣ STEP 2: Crear Tabla CompanyBranding

```bash
php artisan make:migration create_company_brandings_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->onDelete('cascade');
            
            // Imágenes
            $table->string('logo_path')->nullable()->comment('Logo principal (200x60px)');
            $table->string('favicon_path')->nullable()->comment('Favicon (64x64px)');
            $table->string('login_background_path')->nullable()->comment('Fondo login');
            
            // Colores personalizados (JSON)
            $table->json('colores')->default(json_encode([
                'primario' => '#0d6efd',
                'secundario' => '#6c757d',
                'acento' => '#198754',
                'fondo_login' => '#f0f2f5',
                'texto_principal' => '#212529',
                'barra_lateral' => '#1a1d20',
                'barra_navegacion' => '#212529',
                'boton_primario' => '#0d6efd',
                'boton_hover' => '#0b5ed7',
            ]));
            
            // Textos personalizados (JSON)
            $table->json('textos')->default(json_encode([
                'nombre_sistema' => 'Sistema ISTAE',
                'subtitulo' => 'Control Biométrico de Acceso',
                'nombre_empresa' => 'Mi Institución',
                'pie_pagina' => '© 2026 Todos los derechos reservados',
                'mensaje_bienvenida' => 'Bienvenido al Sistema de Control Biométrico',
                'slogan' => 'Seguridad y Control a tu alcance',
            ]));
            
            // Tema
            $table->enum('tema', ['light', 'dark', 'custom'])->default('light');
            $table->boolean('mostrar_marca_agua')->default(false);
            $table->boolean('mostrar_logo_navbar')->default(true);
            
            // Fuente personalizada
            $table->string('fuente_personalizada')->nullable()->comment('URL de Google Fonts');
            
            // Otros
            $table->boolean('mostrar_footer')->default(true);
            $table->string('url_soporte')->nullable();
            $table->string('url_terminos')->nullable();
            $table->string('url_privacidad')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_brandings');
    }
};
```

---

## 3️⃣ STEP 3: Agregar company_id a Tablas Existentes

```bash
php artisan make:migration add_company_id_to_tables
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar a usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->default(1) // ID de empresa por defecto
                ->constrained('companies')
                ->onDelete('cascade');
            
            // Cambiar unique de username a compound key
            $table->unique(['company_id', 'username']);
        });

        // Agregar a logs
        Schema::table('logs', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->default(1)
                ->constrained('companies')
                ->onDelete('cascade');
        });

        // Agregar a permisos
        Schema::table('permisos', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->default(1)
                ->constrained('companies')
                ->onDelete('cascade');
        });

        // Agregar a comandos
        Schema::table('comandos', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->default(1)
                ->constrained('companies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropUnique(['company_id', 'username']);
        });

        Schema::table('logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('permisos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('comandos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
```

---

## 4️⃣ STEP 4: Crear Modelos

### Modelo Company

```php
<?php
// app/Models/Company.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'email',
        'descripcion',
        'subdomain',
        'plan_tipo',
        'fecha_inicio_suscripcion',
        'fecha_fin_suscripcion',
        'activa',
        'limite_usuarios',
        'zona_horaria',
        'idioma',
        'caracteristicas_habilitadas',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'fecha_inicio_suscripcion' => 'date',
        'fecha_fin_suscripcion' => 'date',
        'caracteristicas_habilitadas' => 'array',
    ];

    // Relaciones
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function branding()
    {
        return $this->hasOne(CompanyBranding::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class);
    }

    public function comandos()
    {
        return $this->hasMany(Comando::class);
    }

    // Métodos útiles
    public function suscripcionActiva(): bool
    {
        return $this->activa && now() <= $this->fecha_fin_suscripcion;
    }

    public function usuariosRestantes(): int
    {
        return $this->limite_usuarios - $this->usuarios()->count();
    }

    public function puedeAgregarUsuario(): bool
    {
        return $this->usuariosRestantes() > 0;
    }

    public function diasRestantes(): int
    {
        return now()->diffInDays($this->fecha_fin_suscripcion, false);
    }

    public function tieneCaracteristica(string $caracteristica): bool
    {
        return in_array($caracteristica, $this->caracteristicas_habilitadas ?? []);
    }
}
```

### Modelo CompanyBranding

```php
<?php
// app/Models/CompanyBranding.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBranding extends Model
{
    protected $fillable = [
        'company_id',
        'logo_path',
        'favicon_path',
        'login_background_path',
        'colores',
        'textos',
        'tema',
        'mostrar_marca_agua',
        'mostrar_logo_navbar',
        'fuente_personalizada',
        'mostrar_footer',
        'url_soporte',
        'url_terminos',
        'url_privacidad',
    ];

    protected $casts = [
        'colores' => 'array',
        'textos' => 'array',
        'mostrar_marca_agua' => 'boolean',
        'mostrar_logo_navbar' => 'boolean',
        'mostrar_footer' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Métodos útiles
    public function color(string $key): ?string
    {
        return $this->colores[$key] ?? null;
    }

    public function texto(string $key): ?string
    {
        return $this->textos[$key] ?? null;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset("storage/company-logos/{$this->logo_path}") : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? asset("storage/company-favicons/{$this->favicon_path}") : null;
    }

    public function loginBackgroundUrl(): ?string
    {
        return $this->login_background_path ? asset("storage/company-backgrounds/{$this->login_background_path}") : null;
    }

    public function generarCSS(): string
    {
        $colores = $this->colores;

        return ":root {
            --primary-color: {$colores['primario']};
            --secondary-color: {$colores['secundario']};
            --accent-color: {$colores['acento']};
            --login-bg: {$colores['fondo_login']};
            --text-main: {$colores['texto_principal']};
            --sidebar-bg: {$colores['barra_lateral']};
            --navbar-bg: {$colores['barra_navegacion']};
            --btn-primary: {$colores['boton_primario']};
            --btn-hover: {$colores['boton_hover']};
        }";
    }
}
```

---

## 5️⃣ STEP 5: Crear Middleware MultiTenant

```bash
php artisan make:middleware SetCompanyContext
```

```php
<?php
// app/Http/Middleware/SetCompanyContext.php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class SetCompanyContext
{
    public function handle(Request $request, Closure $next)
    {
        // Opción 1: Obtener company del usuario autenticado
        if (auth()->check()) {
            $company = auth()->user()->company;
        }
        // Opción 2: Obtener company del subdomain
        else {
            $subdomain = $this->getSubdomain($request);
            $company = Company::where('subdomain', $subdomain)->first();
        }

        if (!$company) {
            abort(404, 'Company not found');
        }

        // Guardar en contexto global
        app()->instance('company', $company);

        // Compartir con vistas
        view()->share('company', $company);
        view()->share('branding', $company->branding);

        return $next($request);
    }

    private function getSubdomain(Request $request): string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // Retornar subdominio o 'default'
        return $parts[0] === 'www' ? 'default' : $parts[0];
    }
}
```

**Registrar en `app/Http/Kernel.php`:**

```php
protected $middleware = [
    // ... otros middlewares
    \App\Http\Middleware\SetCompanyContext::class,
];
```

---

## 6️⃣ STEP 6: Actualizar Modelos Existentes

### User Model (Actualizado)

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    // ... código existente ...

    protected $fillable = [
        'company_id',  // ← AGREGAR
        'biometric_id',
        'nombre',
        'username',
        'password',
        'rol',
        'acceso_puerta',
    ];

    // AGREGAR RELACIÓN
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scope para obtener usuarios de la empresa actual
    public function scopeOfCompany($query)
    {
        return $query->where('company_id', app('company')->id);
    }
}
```

### Log Model (Actualizado)

```php
<?php
// app/Models/Log.php

namespace App\Models;

class Log extends Model
{
    protected $fillable = [
        'company_id',  // ← AGREGAR
        'usuario_id',
        'tipo_evento',
        'origen',
        'latitud',
        'longitud',
        'descripcion',
        'foto_path',
        'fecha',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scope
    public function scopeOfCompany($query)
    {
        return $query->where('company_id', app('company')->id);
    }
}
```

---

## 7️⃣ STEP 7: Crear Service para Branding

```php
<?php
// app/Services/BrandingService.php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBranding;

class BrandingService
{
    public function getBranding(Company $company): CompanyBranding
    {
        return $company->branding ?? $this->crearBrandingDefecto($company);
    }

    public function crearBrandingDefecto(Company $company): CompanyBranding
    {
        return CompanyBranding::create([
            'company_id' => $company->id,
            'colores' => [
                'primario' => '#0d6efd',
                'secundario' => '#6c757d',
                'acento' => '#198754',
                'fondo_login' => '#f0f2f5',
                'texto_principal' => '#212529',
                'barra_lateral' => '#1a1d20',
                'barra_navegacion' => '#212529',
                'boton_primario' => '#0d6efd',
                'boton_hover' => '#0b5ed7',
            ],
            'textos' => [
                'nombre_sistema' => 'Sistema ISTAE',
                'subtitulo' => 'Control Biométrico de Acceso',
                'nombre_empresa' => $company->nombre,
                'pie_pagina' => "© 2026 {$company->nombre}",
                'mensaje_bienvenida' => "Bienvenido a {$company->nombre}",
                'slogan' => 'Seguridad y Control a tu alcance',
            ],
        ]);
    }

    public function guardarBranding(Company $company, array $datos): CompanyBranding
    {
        $branding = $this->getBranding($company);

        if (isset($datos['colores'])) {
            $branding->colores = array_merge($branding->colores, $datos['colores']);
        }

        if (isset($datos['textos'])) {
            $branding->textos = array_merge($branding->textos, $datos['textos']);
        }

        if (isset($datos['tema'])) {
            $branding->tema = $datos['tema'];
        }

        $branding->save();

        return $branding;
    }

    public function subirLogo(Company $company, $archivo)
    {
        $path = $archivo->store('company-logos', 'public');
        $branding = $this->getBranding($company);
        $branding->logo_path = basename($path);
        $branding->save();

        return $branding;
    }

    public function generarCSS(CompanyBranding $branding): string
    {
        return $branding->generarCSS();
    }
}
```

---

## 8️⃣ STEP 8: Crear Controller para Branding

```bash
php artisan make:controller Admin/BrandingController
```

```php
<?php
// app/Http/Controllers/Admin/BrandingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\BrandingService;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    private BrandingService $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    public function show()
    {
        $company = auth()->user()->company;
        $branding = $this->brandingService->getBranding($company);

        return view('admin.branding.show', [
            'company' => $company,
            'branding' => $branding,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nombre_sistema' => 'nullable|string|max:100',
            'subtitulo' => 'nullable|string|max:200',
            'nombre_empresa' => 'nullable|string|max:150',
            'pie_pagina' => 'nullable|string|max:300',
            'colores.primario' => 'nullable|regex:/#[a-f0-9]{6}/',
            'colores.secundario' => 'nullable|regex:/#[a-f0-9]{6}/',
            'colores.acento' => 'nullable|regex:/#[a-f0-9]{6}/',
            'tema' => 'in:light,dark,custom',
            'mostrar_marca_agua' => 'boolean',
        ]);

        $company = auth()->user()->company;
        
        // Separar colores y textos
        $colores = collect($validated)
            ->filter(fn($v, $k) => str_starts_with($k, 'colores.'))
            ->mapWithKeys(fn($v, $k) => [str_replace('colores.', '', $k) => $v])
            ->toArray();

        $textos = [
            'nombre_sistema' => $validated['nombre_sistema'] ?? null,
            'subtitulo' => $validated['subtitulo'] ?? null,
            'nombre_empresa' => $validated['nombre_empresa'] ?? null,
            'pie_pagina' => $validated['pie_pagina'] ?? null,
        ];

        $this->brandingService->guardarBranding($company, [
            'colores' => $colores,
            'textos' => array_filter($textos),
            'tema' => $validated['tema'] ?? 'light',
            'mostrar_marca_agua' => $validated['mostrar_marca_agua'] ?? false,
        ]);

        return back()->with('success', 'Branding actualizado correctamente');
    }

    public function subirLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:5120|dimensions:min_width=100',
        ]);

        $company = auth()->user()->company;
        $this->brandingService->subirLogo($company, $request->file('logo'));

        return back()->with('success', 'Logo actualizado correctamente');
    }
}
```

---

## 9️⃣ STEP 9: Crear Vista para Branding

```blade
<!-- resources/views/admin/branding/show.blade.php -->

@extends('base')

@section('title') Configuración de Marca @endsection

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-palette-fill me-2 text-primary"></i>
                Configuración de Marca
            </h5>
        </div>
        <div class="card-body p-4">
            
            <!-- Logo -->
            <div class="mb-5">
                <h6 class="fw-bold mb-3">Logo Principal</h6>
                <form action="{{ route('admin.branding.upload-logo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            @if($branding->logoUrl())
                                <img src="{{ $branding->logoUrl() }}" 
                                     class="img-fluid rounded-3 border" 
                                     style="max-width: 200px;">
                            @else
                                <div class="bg-light rounded-3 p-4 text-center">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">Sin logo</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="logo" class="form-label">Seleccionar Logo</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="logo" 
                                       name="logo" 
                                       accept="image/*"
                                       required>
                                <small class="text-muted">Máximo 5MB, dimensiones recomendadas: 200x60px</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-2"></i>Cargar Logo
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <hr>

            <!-- Colores -->
            <div class="mb-5">
                <h6 class="fw-bold mb-3">🎨 Colores Personalizados</h6>
                <form action="{{ route('admin.branding.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Color Primario</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="colores[primario]" 
                                       class="form-control form-control-color" 
                                       value="{{ $branding->color('primario') }}">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $branding->color('primario') }}" 
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color Secundario</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="colores[secundario]" 
                                       class="form-control form-control-color" 
                                       value="{{ $branding->color('secundario') }}">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $branding->color('secundario') }}" 
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color Acento</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="colores[acento]" 
                                       class="form-control form-control-color" 
                                       value="{{ $branding->color('acento') }}">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $branding->color('acento') }}" 
                                       readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barra Lateral</label>
                            <div class="input-group">
                                <input type="color" 
                                       name="colores[barra_lateral]" 
                                       class="form-control form-control-color" 
                                       value="{{ $branding->color('barra_lateral') }}">
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $branding->color('barra_lateral') }}" 
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Textos -->
                    <hr class="my-5">
                    <h6 class="fw-bold mb-3">✍️ Textos Personalizados</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Sistema</label>
                            <input type="text" 
                                   name="nombre_sistema" 
                                   class="form-control" 
                                   value="{{ $branding->texto('nombre_sistema') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtítulo</label>
                            <input type="text" 
                                   name="subtitulo" 
                                   class="form-control" 
                                   value="{{ $branding->texto('subtitulo') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Pie de Página</label>
                            <textarea name="pie_pagina" 
                                      class="form-control" 
                                      rows="2">{{ $branding->texto('pie_pagina') }}</textarea>
                        </div>
                    </div>

                    <!-- Tema -->
                    <hr class="my-5">
                    <h6 class="fw-bold mb-3">🌙 Tema</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="tema" 
                                       id="tema_light" 
                                       value="light"
                                       @if($branding->tema == 'light') checked @endif>
                                <label class="form-check-label" for="tema_light">Claro</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="tema" 
                                       id="tema_dark" 
                                       value="dark"
                                       @if($branding->tema == 'dark') checked @endif>
                                <label class="form-check-label" for="tema_dark">Oscuro</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="tema" 
                                       id="tema_custom" 
                                       value="custom"
                                       @if($branding->tema == 'custom') checked @endif>
                                <label class="form-check-label" for="tema_custom">Personalizado</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="mostrar_marca_agua" 
                                       id="marca_agua"
                                       value="1"
                                       @if($branding->mostrar_marca_agua) checked @endif>
                                <label class="form-check-label" for="marca_agua">
                                    Mostrar marca de agua
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-5">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 🔟 STEP 10: Actualizar Rutas

```php
// routes/web.php

Route::prefix('admin')->middleware('can:admin')->group(function () {
    // ... rutas existentes ...

    // Nuevas rutas de configuración
    Route::prefix('configuracion')->group(function () {
        Route::get('/branding', [App\Http\Controllers\Admin\BrandingController::class, 'show'])
            ->name('admin.branding.show');
        Route::post('/branding', [App\Http\Controllers\Admin\BrandingController::class, 'update'])
            ->name('admin.branding.update');
        Route::post('/branding/logo', [App\Http\Controllers\Admin\BrandingController::class, 'subirLogo'])
            ->name('admin.branding.upload-logo');
    });
});
```

---

## 📦 PASOS DE EJECUCIÓN

```bash
# 1. Crear migraciones
php artisan make:migration create_companies_table
php artisan make:migration create_company_brandings_table
php artisan make:migration add_company_id_to_tables

# 2. Ejecutar migraciones
php artisan migrate

# 3. Crear Seeder para empresa por defecto
php artisan make:seeder CompanySeeder
```

---

## 🌱 Seeder para Empresa Predeterminada

```php
<?php
// database/seeders/CompanySeeder.php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyBranding;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Crear empresa predeterminada
        $company = Company::create([
            'nombre' => 'ISTAE Default',
            'email' => 'admin@istae.local',
            'descripcion' => 'Empresa predeterminada del sistema',
            'subdomain' => 'default',
            'plan_tipo' => 'professional',
            'fecha_inicio_suscripcion' => now(),
            'fecha_fin_suscripcion' => now()->addYear(),
            'activa' => true,
            'limite_usuarios' => 100,
            'zona_horaria' => 'America/Guayaquil',
            'idioma' => 'es',
            'caracteristicas_habilitadas' => [
                'reportes_avanzados',
                'integraciones',
                'api_access',
                'two_factor_auth',
            ],
        ]);

        // Crear branding predeterminado
        CompanyBranding::create([
            'company_id' => $company->id,
            'tema' => 'light',
        ]);
    }
}
```

**Ejecutar:**
```bash
php artisan db:seed --class=CompanySeeder
```

---

¡Listo! Con estos 10 pasos tienes una base sólida para implementar el sistema multi-tenant.
