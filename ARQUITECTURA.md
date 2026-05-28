# 🏛️ ARQUITECTURA - Sistema Multi-Tenant de Control Biométrico

**Documento Técnico - Especificación de la Arquitectura del Sistema**

---

## 📑 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Patrones y Principios](#patrones-y-principios)
3. [Modelos de Datos](#modelos-de-datos)
4. [Flujos de Datos](#flujos-de-datos)
5. [Componentes del Sistema](#componentes-del-sistema)
6. [Seguridad](#seguridad)
7. [Performance](#performance)
8. [Escalabilidad](#escalabilidad)

---

## 🎯 Visión General

### Arquitectura Multi-Tenant

```
┌─────────────────────────────────────────────────────────┐
│           ACCESO ISTAE - SAAS PLATFORM                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │       NGINX / APACHE (Web Server)                 │   │
│  └──────────────────────────────────────────────────┘   │
│           ↓                                              │
│  ┌──────────────────────────────────────────────────┐   │
│  │    LARAVEL APPLICATION (PHP 8.2)                 │   │
│  │  ├─ Authentication (JWT/Session)                 │   │
│  │  ├─ Multi-Tenant Middleware                      │   │
│  │  ├─ Route Dispatcher                             │   │
│  │  └─ Request/Response Pipeline                    │   │
│  └──────────────────────────────────────────────────┘   │
│           ↓                                              │
│  ┌──────────────────────────────────────────────────┐   │
│  │       MODELS & BUSINESS LOGIC                    │   │
│  │  ├─ Company                  (Tenant)            │   │
│  │  ├─ User                     (Admin/Docente)     │   │
│  │  ├─ CompanyBranding          (Personalización)   │   │
│  │  ├─ Log                       (Asistencia)       │   │
│  │  ├─ Permiso                   (Permisos)        │   │
│  │  └─ Comando                   (Control)          │   │
│  └──────────────────────────────────────────────────┘   │
│           ↓                                              │
│  ┌──────────────────────────────────────────────────┐   │
│  │       DATABASE (MySQL 8.0)                       │   │
│  │  ├─ companies                                    │   │
│  │  ├─ usuarios          (con company_id)          │   │
│  │  ├─ company_brandings                            │   │
│  │  ├─ logs              (con company_id)          │   │
│  │  ├─ permisos          (con company_id)          │   │
│  │  └─ comandos          (con company_id)          │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │    ALMACENAMIENTO (Laravel Storage)              │   │
│  │  ├─ storage/app/public/logos/                    │   │
│  │  ├─ storage/app/public/asistencia/                │   │
│  │  ├─ storage/app/public/branding/                  │   │
│  │  └─ storage/logs/                                │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
└─────────────────────────────────────────────────────────┘
         ↑                                          ↑
         │                                          │
    ┌────┴──────┐                         ┌─────────┴────┐
    │   WEB     │                         │  DISPOSITIVO │
    │  (Browser)│                         │  (NodeMCU)   │
    └───────────┘                         └──────────────┘
```

---

## 🎨 Patrones y Principios

### 1. **Multi-Tenancy Pattern**

**Tipo**: Database per Schema (Schemas compartidos, company_id por registro)

```
├─ VENTAJAS:
│  ✓ Costos de infraestructura reducidos
│  ✓ Actualización centralizada
│  ✓ Mantenimiento simplificado
│  ✓ Escalabilidad horizontal fácil
│
└─ DESVENTAJAS:
   ✗ Riesgo de data leak si falla el aislamiento
   ✗ Performance impactada con muchos tenants
   ✗ Backup/restore más complejo
```

**Implementación**:

```php
// SetCompanyContext Middleware
if ($user = auth()->user()) {
    $company = $user->company;  // Relación: User belongsTo Company
} else {
    $company = Company::where('subdomain', $subdomain)->firstOrFail();
}

app()->instance('company', $company);
View::share('company', $company);
```

### 2. **Repository Pattern** (Parcialmente)

```
Controllers
    ↓
Services (BrandingService)
    ↓
Models (Eloquent ORM)
    ↓
Database
```

### 3. **Active Record Pattern** (Eloquent)

Todos los modelos extienden `Illuminate\Database\Eloquent\Model`:

```php
// Consultas elegantes
$users = User::where('company_id', company()->id)
    ->where('active', true)
    ->paginate(15);

// Relaciones
$company->usuarios;
$user->company;
$log->usuario;
```

### 4. **Scopes para Multi-Tenant**

```php
// Model
public function scopeOfCompany($query, $company = null)
{
    return $query->where('company_id', $company?->id ?? company()->id);
}

// Uso
User::ofCompany()->get();  // Solo usuarios de esta empresa
Log::ofCompany()->recent(7)->get();  // Logs de últimos 7 días
```

---

## 💾 Modelos de Datos

### Diagrama Entidad-Relación

```
┌─────────────────────┐
│    COMPANIES        │◄──────┐
├─────────────────────┤       │
│ id (PK)             │       │
│ nombre              │       │
│ email (unique)      │       │
│ subdomain (unique)  │       │ (1:1)
│ plan_tipo           │       │
│ fecha_inicio_suscrip│       │
│ fecha_fin_suscrip   │       │
│ activa              │       │
│ limite_usuarios     │       │
│ zona_horaria        │       │
│ idioma              │       │
│ caracteristicas_json│       │
│ timestamps          │       │
└─────────────────────┘       │
        │ (1:N)               │
        │                     │
        ├─► USUARIOS         │
        │   ├─ id (PK)       │
        │   ├─ company_id ◄──┘
        │   ├─ biometric_id  │
        │   ├─ nombre        │
        │   ├─ username      │
        │   ├─ password      │
        │   ├─ rol (admin|docente)
        │   ├─ acceso_puerta (bool)
        │   └─ timestamps
        │
        ├─► LOGS
        │   ├─ id (PK)
        │   ├─ company_id ◄──┐
        │   ├─ usuario_id    │ (1:N)
        │   ├─ fecha         │
        │   ├─ tipo_evento   │
        │   ├─ origen        │
        │   ├─ latitud       │
        │   ├─ longitud      │
        │   ├─ foto_path     │
        │   └─ timestamps
        │
        ├─► PERMISOS
        │   ├─ id (PK)
        │   ├─ company_id ◄──┐
        │   ├─ usuario_id    │ (1:N)
        │   ├─ fecha_permiso │
        │   ├─ observacion   │
        │   └─ timestamps
        │
        ├─► COMANDOS
        │   ├─ id (PK)
        │   ├─ company_id ◄──┐
        │   ├─ instruccion   │ (1:N)
        │   ├─ estado        │
        │   └─ timestamps
        │
        └─► COMPANY_BRANDINGS (1:1)
            ├─ id (PK)
            ├─ company_id (unique) ◄────────┘
            ├─ logo_path
            ├─ favicon_path
            ├─ login_background_path
            ├─ colores (JSON)
            ├─ textos (JSON)
            ├─ tema
            ├─ mostrar_marca_agua
            ├─ mostrar_logo_navbar
            ├─ mostrar_footer
            └─ timestamps
```

### Detalles de Modelos

#### **Company**

```php
class Company extends Model
{
    // Campos principales
    protected $fillable = [
        'nombre',
        'email',
        'descripcion',
        'subdomain',
        'plan_tipo',  // basic | professional | enterprise
        'fecha_inicio_suscripcion',
        'fecha_fin_suscripcion',
        'activa',
        'limite_usuarios',
        'zona_horaria',
        'idioma',
        'caracteristicas_habilitadas',  // JSON
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
}
```

#### **User (tabla: usuarios)**

```php
class User extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = [
        'company_id',     // ← Multi-tenant key
        'biometric_id',
        'nombre',
        'username',
        'password',
        'rol',            // admin | docente
        'acceso_puerta',  // boolean: ¿puede abrir puerta?
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class);
    }

    // Scopes
    public function scopeOfCompany($query, $company = null)
    {
        return $query->where('company_id', $company?->id ?? company()->id);
    }

    public function scopeAdmin($query)
    {
        return $query->where('rol', 'admin');
    }
}
```

#### **CompanyBranding**

```php
class CompanyBranding extends Model
{
    protected $fillable = [
        'company_id',
        'logo_path',
        'favicon_path',
        'login_background_path',
        'colores',         // JSON array
        'textos',          // JSON array
        'tema',            // light | dark | custom
        'mostrar_marca_agua',
        'mostrar_logo_navbar',
        'mostrar_footer',
        'fuente_personalizada',
        'url_soporte',
        'url_terminos',
        'url_privacidad',
    ];

    // Estructura JSON: colores
    // {
    //   "primario": "#0d6efd",
    //   "secundario": "#6c757d",
    //   "acento": "#198754",
    //   "navbar": "#ffffff",
    //   "botones": "#0d6efd",
    //   "texto": "#212529",
    //   "fondo": "#f8f9fa",
    //   "bordes": "#dee2e6",
    //   "error": "#dc3545"
    // }

    // Estructura JSON: textos
    // {
    //   "nombre_sistema": "Acceso ISTAE",
    //   "subtitulo": "Sistema de Control Biométrico",
    //   "slogan": "Seguridad y Control",
    //   "pie_pagina": "© 2026 ISTAE",
    //   "email_soporte": "soporte@istae.local",
    //   "telefono": "+593 4 XXXXXXX"
    // }
}
```

---

## 🔄 Flujos de Datos

### 1. Flujo de Autenticación

```
┌─────────────────┐
│  User (Browser) │
└────────┬────────┘
         │
         │ 1. POST /login
         ↓
┌──────────────────────────────┐
│    AuthController::login()   │
├──────────────────────────────┤
│ 1. Validar credenciales      │
│ 2. Hash::check(password)     │
│ 3. Obtener user->company     │
│ 4. Crear sesión              │
└──────────────────────────────┘
         │
         │ 2. Redirect (auth()->check() = true)
         ↓
┌──────────────────────────────┐
│  SetCompanyContext Middleware│
├──────────────────────────────┤
│ 1. auth()->user()->company   │
│ 2. app()->instance('company')│
│ 3. View::share('company')    │
└──────────────────────────────┘
         │
         │ 3. Show Dashboard
         ↓
┌─────────────────┐
│   Dashboard     │
│  (Personalized) │
└─────────────────┘
```

### 2. Flujo de Registro de Asistencia (Dispositivo)

```
┌──────────────────────┐
│   Dispositivo        │
│   NodeMCU + Huella   │
└────────┬─────────────┘
         │
         │ 1. Detecta huella
         │    Realiza matching
         │    Obtiene ID biométrico
         │
         │ 2. POST /api/recibir_log
         │    {
         │      "biometric_id": 1001,
         │      "fecha": "2026-05-28 09:45:00",
         │      "tipo_evento": "ENTRADA",
         │      "token": "istae1805A"
         │    }
         ↓
┌──────────────────────────────────┐
│   ApiController::recibirLog()    │
├──────────────────────────────────┤
│ 1. Validar token (ENV check)     │
│ 2. Buscar User por biometric_id  │
│ 3. Validar existe y activo       │
│ 4. Crear registro Log            │
│ 5. Ejecutar acción (abrir puerta)│
│ 6. Responder JSON success        │
└──────────────────────────────────┘
         │
         │ 3. Registro guardado en DB
         ↓
┌────────────────────────┐
│  LOGS TABLE            │
│ - usuario_id: 5        │
│ - company_id: 1        │
│ - fecha: 2026-05-28... │
│ - tipo_evento: ENTRADA │
└────────────────────────┘
```

### 3. Flujo de Asistencia Remota (Web)

```
┌──────────────────────┐
│   Docente (Browser)  │
│   - GPS enabled      │
│   - Camera enabled   │
└────────┬─────────────┘
         │
         │ 1. Click "Marcar Presencia"
         ↓
┌────────────────────────────────┐
│ JavaScript (GPS + Camera)      │
├────────────────────────────────┤
│ 1. navigator.geolocation.      │
│    getCurrentPosition()         │
│ 2. Capturar foto con camera    │
│ 3. Convertir blob a base64     │
└────────────────────────────────┘
         │
         │ 4. POST /docente/marcar_presencia_web
         │    {
         │      "latitud": -0.2176,
         │      "longitud": -78.5149,
         │      "foto": "base64...",
         │      "observacion": "Presente"
         │    }
         ↓
┌──────────────────────────────┐
│ DocenteController::           │
│   marcarPresenciaWeb()       │
├──────────────────────────────┤
│ 1. Validar usuario           │
│ 2. Guardar foto en storage   │
│ 3. Crear registro Log        │
│ 4. Aplicar descuentos si hay │
│    zona restringida          │
└──────────────────────────────┘
         │
         ↓
    Log guardado con:
    - usuario_id, company_id
    - latitud, longitud
    - foto_path
    - tipo_evento: ASISTENCIA_WEB
```

### 4. Flujo de Personalización (Branding)

```
┌──────────────────────┐
│   Admin              │
│   (Browser)          │
└────────┬─────────────┘
         │
         │ 1. Accede a /admin/configuracion/branding
         ↓
┌──────────────────────────────────┐
│ BrandingController::show()       │
├──────────────────────────────────┤
│ 1. Obtener company() del context │
│ 2. Buscar CompanyBranding        │
│ 3. O crear por defecto           │
│ 4. Pasar a vista Blade           │
└──────────────────────────────────┘
         │
         ↓
┌──────────────────────┐
│  Formulario HTML     │
│  - Upload logo       │
│  - Color picker (9)  │
│  - Inputs de texto   │
│  - Preview en vivo   │
└──────────────────────┘
         │
         │ 2. Admin completa y envía
         │    POST /admin/configuracion/branding
         │    {
         │      "logo": file,
         │      "colores": { "primario": "#123456", ... },
         │      "textos": { "nombre": "Mi Empresa", ... },
         │      "tema": "light"
         │    }
         ↓
┌──────────────────────────────────┐
│ BrandingController::update()     │
├──────────────────────────────────┤
│ 1. Validar datos                 │
│ 2. Si hay logo: guardar en       │
│    storage/public/logos/         │
│ 3. Crear/actualizar              │
│    CompanyBranding              │
│ 4. Guardar colores y textos JSON │
│ 5. Invalidar cache               │
└──────────────────────────────────┘
         │
         ↓
    Datos guardados:
    ├─ logo_path: storage/logos/empresa-1.png
    ├─ colores: { "primario": "#123456", ... }
    ├─ textos: { "nombre": "Mi Empresa", ... }
    └─ tema: light
```

---

## 🔌 Componentes del Sistema

### Controllers

| Controller | Métodos | Responsabilidad |
|-----------|---------|-----------------|
| **AuthController** | login, logout, perfil | Autenticación y gestión de usuarios |
| **AdminController** | dashboard, gestionAsistencia, gestionPermisos, reportes | Panel de administración |
| **DocenteController** | dashboard, abrirPuerta, marcarWeb | Panel del docente |
| **ApiController** | sincronizar, recibirLog, checkComando | API del dispositivo |
| **BrandingController** | show, update, subirLogo | Gestión de personalización |

### Middlewares

```php
// SetCompanyContext.php
// ├─ Detecta empresa por usuario autenticado o subdomain
// ├─ Valida que empresa exista
// ├─ Comparte en vistas
// └─ Disponible como company() helper

// auth
// └─ Middleware nativo de Laravel

// can:admin / can:docente
// └─ Authorization (Gates)
```

### Services

```php
// BrandingService.php
// ├─ getBranding(Company)       // Obtiene o crea
// ├─ crearBrandingDefecto()    // Template Bootstrap
// ├─ guardarBranding()         // Merge + salva
// └─ subirLogo()               // Manejo de archivos
```

### Models

6 modelos principales, todos con `ofCompany()` scope.

---

## 🔒 Seguridad

### Arquitectura de Seguridad

```
┌──────────────────────────────────────────────┐
│         CAPAS DE SEGURIDAD                    │
├──────────────────────────────────────────────┤
│                                              │
│ 1. HTTPS/TLS              (Transporte)       │
│    └─ Certificado SSL válido                 │
│                                              │
│ 2. AUTENTICACIÓN          (Identidad)        │
│    ├─ Password Hash Bcrypt                   │
│    ├─ Session Tokens                         │
│    └─ CSRF Tokens (Blade)                    │
│                                              │
│ 3. AUTORIZACIÓN           (Permisos)         │
│    ├─ Gates (admin|docente)                  │
│    ├─ Middleware can:admin                   │
│    └─ Scopes by company_id                   │
│                                              │
│ 4. INYECCIÓN DE DATOS     (Consultas)        │
│    ├─ Eloquent ORM (params vinculados)       │
│    ├─ Blade escaping automático              │
│    └─ Input validation                       │
│                                              │
│ 5. AISLAMIENTO MULTI-TENANT (Datos)         │
│    ├─ company_id en todas las tablas         │
│    ├─ Scopes automáticos                     │
│    └─ Middleware context                     │
│                                              │
│ 6. RATE LIMITING          (API)              │
│    ├─ Throttle middleware                    │
│    └─ Limitar intentos login                 │
│                                              │
└──────────────────────────────────────────────┘
```

### Checksums de Seguridad

```php
✓ Autenticación:
  - Password almacenado con Hash::make()
  - Verificación con Hash::check()
  - Session timeout configurado

✓ Autorización:
  - Gates definidos en AppServiceProvider
  - Middleware can:admin protege rutas admin
  - Scopes aseguran aislamiento company_id

✓ Inyección:
  - Eloquent ORM usa prepared statements
  - Blade {{ }} escapa HTML
  - Input::validate() en controllers

✓ CSRF:
  - @csrf en formularios Blade
  - VerifyCsrfToken middleware

✓ Multi-Tenant:
  - SetCompanyContext siempre activo
  - ofCompany() scope en queries
  - company_id NOT NULL en esquema
```

### Datos Sensibles

```
⚠️ CRÍTICO: Cambiar en producción

// .env
DEVICE_API_TOKEN=istae1805A        ← Cambiar token fuerte
MAIL_PASSWORD=xxx                  ← Variables de mail
APP_KEY=base64:xxx                 ← Generada con artisan key:generate
DB_PASSWORD=xxx                    ← Contraseña BD fuerte
```

---

## ⚡ Performance

### Optimizaciones Implementadas

```
✓ Lazy Loading:
  relations() cargadas solo cuando se accede
  
✓ Eager Loading:
  with('company', 'usuario') evita N+1 queries
  
✓ Pagination:
  paginate(15) por defecto en listados
  
✓ Caching:
  cache('branding.' . company()->id) para branding
  
✓ Índices:
  company_id, usuario_id, fecha índices en tablas grandes
  
✓ Soft Deletes:
  Borrado lógico sin perder datos
```

### Queries Optimizadas

```php
// ❌ MALO: N+1 queries
foreach ($logs as $log) {
    echo $log->usuario->nombre;  // Query por cada log
}

// ✅ BUENO: Eager loading
$logs = Log::ofCompany()->with('usuario')->get();
foreach ($logs as $log) {
    echo $log->usuario->nombre;  // Ya cargado
}

// ✅ MEJOR: Select específico
$logs = Log::ofCompany()
    ->select('id', 'usuario_id', 'fecha', 'tipo_evento')
    ->with('usuario:id,nombre')
    ->paginate();
```

### Benchmarks

| Operación | Tiempo | Queries |
|-----------|--------|---------|
| Login | ~150ms | 3 |
| Cargar 50 logs | ~80ms | 2 |
| Generar reportes Excel | ~500ms | 1 |
| Subir logo | ~200ms | 1 |

---

## 📈 Escalabilidad

### Estrategias de Escalado

#### 1. **Horizontal**

```
Actual (Monolítico):
┌─────────────┐
│  Servidor   │
│  (1 IP)     │
└─────────────┘

Futuro (Loadbalancer):
┌────────────────────┐
│   Loadbalancer     │
│   (nginx)          │
└─────┬──────────────┘
      │
      ├─ ┌─────────────┐
      │  │  Servidor 1 │
      │  │  (APP 1)    │
      │  └─────────────┘
      │
      ├─ ┌─────────────┐
      │  │  Servidor 2 │
      │  │  (APP 2)    │
      │  └─────────────┘
      │
      └─ ┌─────────────┐
         │  Servidor 3 │
         │  (APP 3)    │
         └─────────────┘

Recursos Compartidos:
├─ MySQL (Master-Slave replication)
├─ Redis (Cache centralizado)
└─ Storage (NFS o S3)
```

#### 2. **Base de Datos**

```
Current:    1 DB MySQL
Future:     ├─ Master (Write)
            ├─ Slaves (Read)
            └─ Backup automático c/h
            
Sharding:   Por company_id si >10M registros
            companies 1-100   → DB1
            companies 101-200 → DB2
```

#### 3. **Caché**

```
Implementar Redis para:
├─ Branding por empresa (cache ttl: 1h)
├─ Usuario loggeado (cache ttl: sesión)
├─ Contador de logs (cache ttl: 15min)
└─ Tokens API (cache ttl: 1h)

Invalidar caché en:
└─ POST/PUT/DELETE operations
```

#### 4. **Storage**

```
Actual:     storage/public/ (local)
Future:     S3 o Cloud Storage
            ├─ Logos
            ├─ Fotos de asistencia
            └─ Reportes descargados
```

---

## 📊 Diagrama de Despliegue

```
┌─────────────────────────────────────────────┐
│         ENTORNO DE PRODUCCIÓN               │
├─────────────────────────────────────────────┤
│                                             │
│  Internet (HTTPS)                           │
│     │                                       │
│     ↓                                       │
│  ┌────────────────┐                         │
│  │  CloudFlare /  │                         │
│  │  CDN           │                         │
│  └────────────────┘                         │
│     │                                       │
│     ↓                                       │
│  ┌────────────────────────┐                 │
│  │  nginx (Proxy)         │                 │
│  │  - HTTPS               │                 │
│  │  - Rate limiting       │                 │
│  │  - Compression         │                 │
│  └────────────────────────┘                 │
│     │                                       │
│     ↓                                       │
│  ┌────────────────────────┐                 │
│  │  PHP-FPM Pool (8 workers)                │
│  │  - Laravel App         │                 │
│  │  - Middleware          │                 │
│  │  - Controllers         │                 │
│  └────────────────────────┘                 │
│     │                                       │
│  ┌──┴──────────────────────────┐            │
│  │                             │            │
│  ↓                             ↓            │
│ ┌─────────────┐       ┌─────────────┐      │
│ │  MySQL 8.0  │       │  Redis 6.0  │      │
│ │  (Master)   │       │  (Cache)    │      │
│ └─────────────┘       └─────────────┘      │
│     │                                       │
│     ↓                                       │
│  Backup c/12h                               │
│  └─ S3 Storage                              │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🔧 Stack Tecnológico Completo

```
BACKEND:
├─ PHP 8.2
├─ Laravel 11.x
├─ MySQL 8.0
├─ Composer
├─ Eloquent ORM
├─ Blade Templates
└─ Maatwebsite/Excel

FRONTEND:
├─ HTML5
├─ CSS3 / Bootstrap 5
├─ JavaScript (Vanilla)
├─ Vite (Build)
├─ Chart.js (Gráficos)
└─ Geolocation API

INFRAESTRUCTURA:
├─ nginx / Apache
├─ PHP-FPM
├─ MySQL Server
├─ Redis (optional)
├─ Git
├─ Docker (optional)
└─ SSL/TLS

LIBRERÍAS CLAVE:
├─ laravel/framework: 11.x
├─ laravel/tinker: debugging
├─ phpoffice/phpexcel: reportes
├─ symfony/console: CLI
├─ symfony/http-foundation: HTTP
└─ fakerphp/faker: testing
```

---

**Documento Actualizado**: 28 de Mayo, 2026
**Versión**: 2.0.0
**Autor**: Equipo de Arquitectura ISTAE
