# 🎯 FUNCIONALIDADES - Sistema Multi-Tenant de Control Biométrico

**Guía Detallada de Cada Módulo y Características del Sistema**

---

## 📑 Tabla de Contenidos

1. [Módulo de Autenticación](#módulo-de-autenticación)
2. [Panel de Administración](#panel-de-administración)
3. [Panel de Docentes](#panel-de-docentes)
4. [Sistema de Branding](#sistema-de-branding)
5. [Gestión de Asistencia](#gestión-de-asistencia)
6. [Gestión de Permisos](#gestión-de-permisos)
7. [API de Dispositivos](#api-de-dispositivos)
8. [Reportes y Exportación](#reportes-y-exportación)
9. [Configuración Multi-Tenant](#configuración-multi-tenant)

---

## 🔑 Módulo de Autenticación

### Características

#### 1. **Login**

**Ruta**: `GET/POST /login`

**Funcionalidad**:
- Autenticación con username y password
- Validación de credenciales
- Creación de sesión segura
- Redirección según rol

```php
// AuthController.php
public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $request->session()->regenerate();
        
        // Redirigir según rol
        return match($user->rol) {
            'admin' => redirect('/admin/dashboard'),
            'docente' => redirect('/docente/dashboard'),
            default => redirect('/'),
        };
    }

    return back()->withErrors([
        'username' => 'Credenciales inválidas',
    ]);
}
```

**Validaciones**:
- ✓ Username requerido
- ✓ Password requerido
- ✓ Mínimo 6 caracteres en password
- ✓ Usuario activo en la empresa
- ✓ Empresa activa

**Seguridad**:
- Password almacenado con Bcrypt
- CSRF protection en formulario
- Session regeneration post-login

#### 2. **Logout**

**Ruta**: `GET /logout`

**Funcionalidad**:
- Destruir sesión
- Limpiar cookies
- Redirigir a login

```php
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
}
```

#### 3. **Perfil de Usuario**

**Ruta**: `GET /perfil`

**Funcionalidad**:
- Ver información personal
- Cambiar contraseña
- Actualizar datos básicos

```html
<!-- Información mostrada -->
- Nombre completo
- Username
- Email (si aplica)
- Rol actual
- Empresa (empresa actual)
- Fecha de última sesión
```

#### 4. **Actualizar Contraseña**

**Ruta**: `POST /actualizar_password`

**Payload**:
```json
{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña",
  "new_password_confirmation": "nueva_contraseña"
}
```

**Validaciones**:
- ✓ Password actual correcto
- ✓ Password nuevo diferente del actual
- ✓ Password nuevo ≥ 6 caracteres
- ✓ Confirmación coincide

---

## 👨‍💼 Panel de Administración

### Acceso y Permisos

```
URL: /admin/dashboard
Middleware: auth + can:admin
Roles permitidos: admin
```

### Funcionalidades

#### 1. **Dashboard Principal**

**Ruta**: `GET /admin/dashboard`

**Información Mostrada**:

```
┌─────────────────────────────────────────┐
│  DASHBOARD ADMIN - [Nombre Empresa]     │
├─────────────────────────────────────────┤
│                                         │
│  ┌──────────┐ ┌──────────┐             │
│  │Presentes │ │Ausentes  │ Hoy        │
│  │  45      │ │  10      │             │
│  └──────────┘ └──────────┘             │
│                                         │
│  ┌──────────┐ ┌──────────┐             │
│  │Conectado │ │Sincroniz.│ Dispositivo│
│  │  ✓ OK    │ │  Hora    │             │
│  └──────────┘ └──────────┘             │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │ ÚLTIMAS 10 ACCESOS              │   │
│  ├─────────────────────────────────┤   │
│  │ 09:45 | Juan Pérez | ENTRADA   │   │
│  │ 09:43 | María López | ENTRADA  │   │
│  │ 09:40 | Carlos Ruiz | SALIDA   │   │
│  └─────────────────────────────────┘   │
│                                         │
│  [Gestion Asistencia] [Gestion Permisos]│
│  [Reportes] [Configuracion]             │
│                                         │
└─────────────────────────────────────────┘
```

**Métricas Calculadas**:
- Total presentes/ausentes hoy
- Logs más recientes
- Estado del dispositivo (conectado/desconectado)
- Última sincronización de hora

#### 2. **Gestión de Asistencia**

**Ruta**: `GET /admin/gestion_asistencia`

**Características**:

```
FILTROS:
├─ Por fecha (desde - hasta)
├─ Por usuario (docente/admin)
├─ Por tipo evento (ENTRADA, SALIDA, ASISTENCIA_WEB)
└─ Por estado (confirmado, pendiente)

ACCIONES:
├─ 📝 Editar registro
├─ 🗑️ Eliminar registro
├─ 📊 Descargar Excel
└─ 🔍 Ver detalles
```

**Campos Mostrados**:
```html
- Fecha/Hora del evento
- Usuario (nombre)
- Tipo evento
- Origen (dispositivo/web)
- Geolocalización (si web)
- Foto (si web)
- Acciones
```

**Edición de Asistencia**:

```
GET  /admin/asistencia/editar/{id}      → Formulario
POST /admin/asistencia/actualizar       → Guardar cambios
GET  /admin/asistencia/eliminar/{id}    → Eliminar

Campos editables:
├─ Fecha y hora
├─ Usuario
├─ Tipo evento
└─ Observaciones
```

#### 3. **Gestión de Permisos**

**Ruta**: `GET /admin/gestion_permisos`

**Funcionalidades**:

```
CREAR PERMISO:
┌─────────────────────────────────────┐
│ Formulario: Nuevo Permiso           │
├─────────────────────────────────────┤
│ Usuario:          [Selector]        │
│ Fecha permiso:    [Date picker]     │
│ Tipo:             [radio buttons]   │
│   ○ Licencia      (1+ días)         │
│   ○ Comisión      (horas)           │
│   ○ Permiso       (horas)           │
│ Observación:      [Textarea]        │
│                                     │
│ [Cancelar] [Guardar]                │
└─────────────────────────────────────┘
```

**Validaciones**:
- ✓ Usuario seleccionado
- ✓ Fecha válida (no pasada)
- ✓ Máximo permisos por usuario por mes
- ✓ No solapamiento de permisos

**Estados de Permiso**:
```
PENDIENTE → APROBADO → ACTIVO → COMPLETADO
            └─ RECHAZADO
```

**CRUD Completo**:
- `POST /permiso/crear` - Crear nuevo
- `GET /permiso/editar/{id}` - Formulario edición
- `POST /permiso/actualizar` - Guardar cambios
- `GET /permiso/eliminar/{id}` - Eliminar (soft delete)

#### 4. **Sincronizar Hora del Dispositivo**

**Ruta**: `POST /admin/sincronizar_hora`

**Funcionalidad**:
- Enviar comando SET_TIME al dispositivo
- Sincronizar reloj RTC DS3231
- Validar respuesta del dispositivo

```php
public function sincronizarHora(Request $request)
{
    // Crear comando
    $comando = Comando::ofCompany()->create([
        'instruccion' => 'SET_TIME',
        'estado' => 'PENDIENTE',
        'timestamp' => now()->timestamp,
    ]);

    // Respuesta
    return response()->json([
        'success' => true,
        'comando_id' => $comando->id,
        'timestamp_enviado' => $comando->timestamp,
    ]);
}
```

#### 5. **Abrir Puerta Remota**

**Ruta**: `GET /admin/abrir`

**Funcionalidad**:
- Enviar comando ABRIR a dispositivo
- Registro de apertura (log)
- Confirmación auditada

```php
public function abrirPuerta(Request $request)
{
    $comando = Comando::ofCompany()->create([
        'instruccion' => 'ABRIR',
        'estado' => 'PENDIENTE',
        'usuario_id' => auth()->id(),
    ]);

    Log::ofCompany()->create([
        'usuario_id' => auth()->id(),
        'tipo_evento' => 'APERTURA_REMOTA',
        'origen' => 'web',
        'observacion' => 'Apertura manual por admin',
    ]);

    return back()->with('success', 'Puerta abierta');
}
```

---

## 👨‍🏫 Panel de Docentes

### Acceso y Permisos

```
URL: /docente/dashboard
Middleware: auth + can:docente
Roles permitidos: docente
```

### Funcionalidades

#### 1. **Dashboard del Docente**

**Información**:
```
┌──────────────────────────────────────┐
│ BIENVENIDO, [Nombre Docente]         │
├──────────────────────────────────────┤
│                                      │
│ Mis Datos:                           │
│ ├─ Nombre: Juan Pérez               │
│ ├─ Cédula: 1234567890               │
│ ├─ Rol: Docente                     │
│ └─ Empresa: ISTAE                   │
│                                      │
│ Mis Permisos Activos:                │
│ ├─ Comisión (hoy hasta 14:00)       │
│ └─ Licencia (25-28 mayo)             │
│                                      │
│ Acciones Rápidas:                    │
│ ├─ [Marcar Presencia Web]            │
│ ├─ [Abrir Puerta] (si autorizado)   │
│ └─ [Ver mis Asistencias]             │
│                                      │
│ Mis Últimas Asistencias:             │
│ ├─ 28/05/2026 09:45 - ENTRADA      │
│ ├─ 27/05/2026 16:30 - SALIDA       │
│ └─ ...                              │
│                                      │
└──────────────────────────────────────┘
```

#### 2. **Marcar Asistencia Remota (Web)**

**Ruta**: `POST /docente/marcar_presencia_web`

**Requisitos**:
- Geolocalización habilitada
- Cámara habilitada (opcional para foto)
- Navegador moderno

**Proceso**:

```javascript
// Frontend (JavaScript)
const marcarPresencia = async () => {
    // 1. Obtener GPS
    const position = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    });

    const { latitude, longitude, accuracy } = position.coords;

    // 2. Capturar foto (opcional)
    let fotoBase64 = null;
    if (cameraConsent) {
        const canvas = await capturarFoto();
        fotoBase64 = canvas.toDataURL('image/jpeg');
    }

    // 3. Enviar al servidor
    const response = await fetch('/docente/marcar_presencia_web', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            latitud: latitude,
            longitud: longitude,
            precision: accuracy,
            foto: fotoBase64,
            observacion: 'Presente',
        }),
    });

    return response.json();
};
```

**Backend - Crear Log**:

```php
public function marcarPresenciaWeb(Request $request)
{
    $data = $request->validate([
        'latitud' => ['required', 'numeric'],
        'longitud' => ['required', 'numeric'],
        'precision' => ['required', 'numeric'],
        'foto' => ['nullable', 'string'],
        'observacion' => ['nullable', 'string'],
    ]);

    $usuario = auth()->user();

    // Guardar foto si existe
    $fotoPath = null;
    if ($data['foto']) {
        $fotoPath = $this->guardarFoto(
            $data['foto'],
            $usuario->id
        );
    }

    // Crear log de asistencia
    $log = Log::ofCompany()->create([
        'usuario_id' => $usuario->id,
        'tipo_evento' => 'ASISTENCIA_WEB',
        'origen' => 'web',
        'latitud' => $data['latitud'],
        'longitud' => $data['longitud'],
        'precision' => $data['precision'],
        'foto_path' => $fotoPath,
        'observacion' => $data['observacion'],
    ]);

    return response()->json([
        'success' => true,
        'log_id' => $log->id,
        'mensaje' => 'Asistencia registrada correctamente',
    ]);
}
```

**Validaciones**:
- ✓ Precisión de GPS ≤ 100m
- ✓ Usuario no tiene permiso activo que permite ausencia
- ✓ Horario dentro de jornada laboral
- ✓ Máximo 1 asistencia web por día por usuario

#### 3. **Abrir Puerta (Si Autorizado)**

**Ruta**: `GET /docente/abrir_puerta`

**Condiciones**:
- Usuario tiene `acceso_puerta = 1`
- Comprobación biométrica previa (opcional)
- Comando enviado al dispositivo

```php
if (auth()->user()->acceso_puerta) {
    // Mostrar botón en dashboard
}
```

---

## 🎨 Sistema de Branding

### Personalización Visual por Empresa

**Ruta**: `/admin/configuracion/branding`

**Acceso**: Solo admin de la empresa

### Elementos Personalizables

#### 1. **Logo de Empresa**

```
Especificaciones:
├─ Formato: PNG, JPG, WEBP
├─ Tamaño máximo: 2MB
├─ Dimensiones recomendadas: 200x60px
├─ Ubicación: storage/public/logos/
└─ Uso: Navbar, Login, Reportes

Validación:
├─ Tipo MIME: image/*
├─ Ancho ≥ 100px
├─ Alto ≥ 30px
└─ Aspect ratio: entre 1:1 y 5:1
```

#### 2. **Favicon**

```
Especificaciones:
├─ Formato: ICO, PNG
├─ Tamaño: 64x64px
├─ Ubicación: public/favicon.ico
└─ Uso: Tab del navegador

Automáticamente:
└─ Referenciado en <head>
```

#### 3. **Fondo de Login**

```
Especificaciones:
├─ Formato: PNG, JPG, WEBP
├─ Tamaño máximo: 5MB
├─ Resolución: 1920x1080px mínimo
├─ Ubicación: storage/public/backgrounds/
└─ CSS: background-image property

Aplicación:
└─ <body style="background-image: url({{ branding.login_bg }})">
```

#### 4. **Colores Personalizados**

```
Paleta Configurable (9 colores):

{
  "primario": "#0d6efd",       // Botones principales
  "secundario": "#6c757d",     // Elementos secundarios
  "acento": "#198754",         // Destacados
  "navbar": "#ffffff",         // Fondo navbar
  "botones": "#0d6efd",        // Color botones
  "texto": "#212529",          // Texto principal
  "fondo": "#f8f9fa",          // Fondo de página
  "bordes": "#dee2e6",         // Líneas/bordes
  "error": "#dc3545"           // Alertas de error
}
```

**Uso en CSS**:
```css
/* styles.php (dinámico) */
:root {
  --color-primario: {{ branding.color('primario') }};
  --color-secundario: {{ branding.color('secundario') }};
  --color-acento: {{ branding.color('acento') }};
}

.btn-primary {
  background-color: var(--color-primario);
}
```

#### 5. **Textos Personalizados**

```
Campos Configurables:

{
  "nombre_sistema": "Acceso ISTAE",
  "subtitulo": "Sistema de Control Biométrico",
  "slogan": "Seguridad y Control",
  "pie_pagina": "© 2026 ISTAE. Todos los derechos reservados.",
  "email_soporte": "soporte@istae.local",
  "telefono": "+593 4 XXXXXXX"
}
```

**Ubicaciones en UI**:
```html
<!-- Navbar -->
<h1>{{ branding.texto('nombre_sistema') }}</h1>
<p>{{ branding.texto('subtitulo') }}</p>

<!-- Login -->
<p class="slogan">{{ branding.texto('slogan') }}</p>

<!-- Footer -->
<footer>
  {{ branding.texto('pie_pagina') }}
  <a href="mailto:{{ branding.texto('email_soporte') }}">Soporte</a>
</footer>
```

#### 6. **Tema Visual**

```
Opciones:
├─ light   (Light mode - defecto)
├─ dark    (Modo oscuro)
└─ custom  (Personalizado)

Selector:
<select name="tema">
  <option value="light">Claro</option>
  <option value="dark">Oscuro</option>
  <option value="custom">Personalizado</option>
</select>

Aplicación en CSS:
body.theme-{{ branding.tema }} {
  color-scheme: {{ branding.tema }};
}
```

#### 7. **Opciones de Visualización**

```
Checkboxes:

☑ Mostrar logo en navbar
☑ Mostrar marca de agua
☑ Mostrar footer
☑ Mostrar URL soporte
☐ Mostrar URL términos
```

#### 8. **URLs Adicionales**

```
Configurables:

- URL de soporte: https://soporte.empresa.com
- URL términos: https://empresa.com/terminos
- URL privacidad: https://empresa.com/privacidad
- URL de comunidad: https://comunidad.empresa.com
```

### Flujo de Personalización

```
1. Admin accede: /admin/configuracion/branding
   ↓
2. BrandingController::show() 
   └─ Cargar CompanyBranding o crear por defecto
   ↓
3. Mostrar formulario con vista previa en vivo
   ↓
4. Admin completa datos (logo, colores, textos)
   ↓
5. POST /admin/configuracion/branding
   ├─ Validar datos
   ├─ Guardar logo en storage
   ├─ Actualizar CompanyBranding (JSON)
   └─ Invalidar caché
   ↓
6. Todas las vistas usan: {{ company.branding }}
   ├─ CSS dinámico
   ├─ Textos personalizados
   └─ Logo cargado
```

---

## 📊 Gestión de Asistencia

### Registro Automático (Dispositivo)

**Flujo**:
```
Dispositivo → Lee Huella → Busca Usuario → Envía Log
                                ↓
                           /api/recibir_log
                                ↓
                          ApiController::recibirLog()
                                ↓
                        Crea registro en logs table
                                ↓
                        Abre puerta (comando)
```

### Registro Manual (Admin)

**CRUD Completo**:
- Create: `/admin/gestion_asistencia` + form modal
- Read: Listar con filtros
- Update: `/admin/asistencia/editar/{id}`
- Delete: `/admin/asistencia/eliminar/{id}` (soft)

### Reportes de Asistencia

**Reporte Matricial**:
```
GET /admin/reporte_matricial

Parámetros:
├─ fecha_inicio (requerido)
├─ fecha_fin (requerido)
└─ usuarios[] (opcional)

Salida:
└─ archivo.xlsx
    ├─ Filas: Usuarios
    ├─ Columnas: Fechas (28/5 a 31/5)
    └─ Valores: P (presente) / A (ausente) / L (licencia)
```

**Formato Excel**:
```
┌─────────────┬─────────┬─────────┬─────────┬─────────┐
│ Docente     │ 28/05   │ 29/05   │ 30/05   │ 31/05   │
├─────────────┼─────────┼─────────┼─────────┼─────────┤
│ Juan Pérez  │    P    │    P    │    P    │    A    │
│ María López │    P    │    L    │    L    │    P    │
│ Carlos Ruiz │    A    │    P    │    P    │    P    │
└─────────────┴─────────┴─────────┴─────────┴─────────┘
```

---

## 📋 Gestión de Permisos

### Tipos de Permiso

```
1. LICENCIA (1+ días)
   - Ausencia justificada por varios días
   - Requiere aprobación admin
   - Excluye de reportes de asistencia

2. COMISIÓN (horas)
   - Ausencia por trabajo fuera de institución
   - Parcial del día
   - Registrable en reportes como "C"

3. PERMISO (horas)
   - Salida esporádica
   - Menos de 1 día
   - Justificación requerida
```

### CRUD de Permisos

**Crear**:
```
POST /admin/permiso/crear

{
  "usuario_id": 5,
  "fecha_permiso": "2026-05-29",
  "tipo": "comisión",
  "hora_inicio": "10:00",
  "hora_fin": "14:00",
  "observacion": "Reunión en rectorado"
}

Validaciones:
├─ Usuario existe y pertenece a company
├─ Fecha no es pasada
├─ No existe otro permiso solapado
└─ Tipo válido
```

**Leer**:
```
GET /admin/gestion_permisos

Filtros:
├─ Por fecha (desde - hasta)
├─ Por usuario
├─ Por tipo
├─ Por estado (pendiente/aprobado/rechazado)
└─ Paginación: 15 por página
```

**Actualizar**:
```
GET  /admin/permiso/editar/{id}   → Formulario
POST /admin/permiso/actualizar    → Guardar

Campos editables:
├─ Fecha
├─ Tipo
├─ Observación
└─ Estado
```

**Eliminar**:
```
GET /admin/permiso/eliminar/{id}

├─ Soft delete (marca como deleted_at)
├─ Reversible
└─ Auditable
```

---

## 🔗 API de Dispositivos

### Endpoints REST

#### 1. **Sincronizar Usuarios**

```
GET /api/sincronizar?token=istae1805A

Respuesta:
[
  {
    "id": 1001,
    "nombre": "Juan Pérez",
    "acceso_puerta": 1
  },
  {
    "id": 1002,
    "nombre": "María López",
    "acceso_puerta": 0
  },
  ...
]
```

#### 2. **Recibir Log de Asistencia**

```
POST /api/recibir_log

Body:
{
  "biometric_id": 1001,
  "fecha": "2026-05-28 09:45:23",
  "tipo_evento": "ENTRADA",
  "token": "istae1805A"
}

Respuesta:
{
  "success": true,
  "log_id": 1234,
  "usuario": "Juan Pérez",
  "accion": "ABRIR"  // o "DENEGAR"
}

Códigos HTTP:
├─ 200 OK        (Log registrado)
├─ 401 Unauthorized (Token inválido)
├─ 404 Not Found (Usuario no existe)
└─ 422 Unprocessable (Datos inválidos)
```

#### 3. **Consultar Comando**

```
GET /api/check_comando?biometric_id=1001&token=istae1805A

Respuesta (próximo comando pendiente):
{
  "comando_id": 567,
  "instruccion": "SET_TIME",
  "timestamp": 1716900323,
  "datos": {
    "hora": "2026-05-28",
    "minuto": 15,
    "segundo": 45
  }
}

Si no hay comandos:
{
  "comando_id": null,
  "instruccion": "WAIT"
}
```

### Seguridad API

```
✓ Token en header: X-API-Token
✓ Rate limiting: 100 requests/min por token
✓ IP whitelist (opcional)
✓ Validación de firma (SHA256)
✓ Log de todas las operaciones
```

---

## 📈 Reportes y Exportación

### Tipos de Reportes

#### 1. **Reporte Matricial de Asistencia**

```
Formato: Excel (.xlsx)
Parámetros:
├─ Rango de fechas
├─ Docentes (múltiple select)
└─ Consolidado: Sí/No

Contenido:
├─ Hoja 1: Asistencia matricial
├─ Hoja 2: Resumen (total P/A/L)
├─ Hoja 3: Justificaciones
└─ Encabezado: Logo, empresa, período
```

**Descarga**:
```
GET /admin/reporte_matricial?fecha_inicio=2026-05-01&fecha_fin=2026-05-31
→ archivo.xlsx
```

#### 2. **Reporte de Permisos**

```
Formato: Excel (.xlsx)
Contenido:
├─ Encabezado: Período, empresa
├─ Columnas: Usuario, Fecha, Tipo, Estado
├─ Filtros aplicados
└─ Total licencias/comisiones/permisos

Ejemplo:
┌────────────┬────────────┬──────────┬─────────┐
│ Usuario    │ Fecha      │ Tipo     │ Estado  │
├────────────┼────────────┼──────────┼─────────┤
│ Juan Pérez │ 28/05/2026 │ Comisión │ Aprobad │
│ María López│ 29-30/05   │ Licencia │ Aprobad │
└────────────┴────────────┴──────────┴─────────┘
```

**Descarga**:
```
GET /admin/reporte_permisos?estado=aprobado
→ archivo.xlsx
```

### Exportación de Datos

**Formato CSV** (opcional):
```
GET /admin/gestion_asistencia/export_csv
└─ descarga.csv
```

**Formato JSON** (para integración):
```
GET /api/logs.json?empresa_id=1&fecha_inicio=...
→ [{ id, usuario, fecha, tipo, ... }, ...]
```

---

## 🏢 Configuración Multi-Tenant

### Gestión de Empresas (Superadmin)

```
Rutas futuras:
GET    /superadmin/empresas              → Listar
POST   /superadmin/empresas              → Crear
GET    /superadmin/empresas/{id}         → Detalle
PUT    /superadmin/empresas/{id}         → Actualizar
DELETE /superadmin/empresas/{id}         → Eliminar
GET    /superadmin/empresas/{id}/users   → Usuarios
```

**Datos de Empresa**:
```
{
  "nombre": "ISTAE",
  "email": "admin@istae.local",
  "subdomain": "istae",
  "plan_tipo": "enterprise",
  "fecha_inicio": "2026-01-01",
  "fecha_fin": "2026-12-31",
  "activa": true,
  "limite_usuarios": 200,
  "zona_horaria": "America/Guayaquil",
  "idioma": "es"
}
```

### Gestión de Usuarios por Empresa

```
Rutas:
GET    /admin/usuarios              → Listar (solo de la empresa)
POST   /admin/usuarios              → Crear
GET    /admin/usuarios/{id}         → Detalle
PUT    /admin/usuarios/{id}         → Actualizar
DELETE /admin/usuarios/{id}         → Eliminar (soft)

Campos:
├─ nombre
├─ username (único por empresa)
├─ password (auto-generada)
├─ rol (admin | docente)
├─ acceso_puerta (bool)
├─ biometric_id (único global)
└─ activo (bool)
```

---

## 📱 Vistas Principales

### Estructura de Vistas (Blade)

```
resources/views/
├─ base.blade.php           (Layout principal)
├─ admin.blade.php          (Admin layout)
├─ docente.blade.php        (Docente layout)
├─ admin/
│  ├─ dashboard.blade.php
│  ├─ gestion_asistencia.blade.php
│  ├─ gestion_permisos.blade.php
│  ├─ editar_asistencia.blade.php
│  └─ editar_permiso.blade.php
├─ docente/
│  ├─ dashboard.blade.php
│  └─ marcar_web.blade.php
├─ branding/
│  └─ configuracion.blade.php
└─ auth/
   └─ login.blade.php
```

### Componentes Reutilizables

```
@include('components.navbar')      → Navbar con logo
@include('components.sidebar')     → Menú lateral
@include('components.alerts')      → Alertas/notificaciones
@include('components.modal')       → Modal genérico
@include('components.pagination')  → Paginación
```

---

## 🎓 Ejemplos de Uso

### Crear Nuevo Usuario

```php
// AdminController
public function crearDocente(Request $request)
{
    $data = $request->validate([
        'nombre' => 'required|string|max:100',
        'username' => 'required|string|unique:usuarios,username,null,id,company_id,' . company()->id,
        'password' => 'required|min:6|confirmed',
        'rol' => 'required|in:admin,docente',
        'biometric_id' => 'required|unique:usuarios',
        'acceso_puerta' => 'boolean',
    ]);

    $usuario = User::ofCompany()->create([
        'company_id' => company()->id,
        ...$data,
        'password' => Hash::make($data['password']),
    ]);

    return redirect()->route('gestion_usuarios')
        ->with('success', "Docente {$usuario->nombre} creado");
}
```

### Generar Reporte Excel

```php
// AdminController
public function descargarReporteMatricial(Request $request)
{
    $logs = Log::ofCompany()
        ->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin])
        ->with('usuario')
        ->get();

    return new AsistenciaExport($logs, $request->fecha_inicio, $request->fecha_fin);
}

// app/Exports/AsistenciaExport.php
use Maatwebsite\Excel\Concerns\FromCollection;

class AsistenciaExport implements FromCollection
{
    public function collection()
    {
        // ... lógica de matricial
    }
}
```

---

**Documento Actualizado**: 28 de Mayo, 2026
**Versión**: 2.0.0
**Autor**: Equipo de Desarrollo ISTAE
