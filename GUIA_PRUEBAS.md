# 🧪 GUÍA DE PRUEBA Y VERIFICACIÓN

**Documento para validar los cambios realizados en Branding, AdminController y Vistas Blade**

---

## 📋 Checklist de Verificación

### ✅ 1. Sistema de Branding

#### Prueba: Subir Logo

```
Pasos:
1. Acceder a /admin/configuracion/branding
2. Hacer click en "Cambiar Logo"
3. Seleccionar una imagen:
   - ✓ JPG, PNG, WEBP
   - ✓ Tamaño: 100x50px mínimo, 1000x500px máximo
   - ✓ Peso: máximo 5MB

Resultado esperado:
- Logo se carga correctamente en navbar
- Logo antiguo se reemplaza/elimina
- Mensaje de éxito: "Logo actualizado correctamente"
```

#### Prueba: Cambiar Colores

```
Pasos:
1. En /admin/configuracion/branding
2. Llenar colores en formato hexadecimal (#XXXXXX)
3. Cambiar tema (light/dark/custom)
4. Guardar cambios

Validaciones esperadas:
- ✓ Rechaza colores sin # 
- ✓ Rechaza colores con formato inválido
- ✓ Acepta #0d6efd, #ffffff, #000000
- ✓ Rechaza #gggggg o #12345
- Mensaje: "Branding actualizado correctamente"
```

#### Prueba: Actualizar Textos

```
Pasos:
1. Rellenar campos de texto:
   - Nombre del sistema (mín 3 caracteres)
   - Subtítulo (mín 3 caracteres)
   - Pie de página (mín 5 caracteres)
   - Email de soporte (validación email)
   - Teléfono (máximo 20 caracteres)

Validaciones esperadas:
- ✓ Rechaza textos menores a mínimo
- ✓ Rechaza emails inválidos
- ✓ Los textos se reflejan en toda la app
```

---

### ✅ 2. AdminController - Validaciones

#### Prueba: Crear Docente

```
Ruta: /admin/dashboard → Crear Docente

Campos:
- ID Biométrico: 1001 (único)
- Nombre: Juan Pérez (solo letras, espacios, guiones)
- Username: juan.perez (único)
- Password: Pass123456 (8+ chars, mayús, minús, números)
- Confirmar Password

Validaciones esperadas:
✓ Rechaza ID duplicado
✓ Rechaza username con caracteres especiales
✓ Rechaza password sin mayúsculas
✓ Rechaza password sin números
✓ Rechaza password < 8 caracteres
✓ Rechaza confirmación diferente

Mensaje: "Docente 'Juan Pérez' creado correctamente"
```

#### Prueba: Editar Asistencia

```
Ruta: Gestión Asistencia → Editar registro

Cambios a realizar:
1. Cambiar fecha a anterior
2. Cambiar tipo evento (ENTRADA→SALIDA)
3. Cambiar origen (dispositivo→manual)
4. Agregar descripción

Validaciones esperadas:
✓ Acepta fechas válidas
✓ Rechaza tipos inválidos
✓ Rechaza origen inválido
✓ Muestra errores en red (all() y $errors)

Mensaje: "Asistencia actualizada correctamente"
```

#### Prueba: Crear Permiso

```
Ruta: Gestión Permisos → Crear Permiso

Campos:
- Docente: (selector)
- Fecha: (date picker)
- Tipo: licencia/comisión/permiso

Validaciones esperadas:
✓ Rechaza fechas pasadas (after_or_equal:today)
✓ Rechaza sin docente
✓ Detecta permisos solapados (fecha duplicada)
✓ Almacena company_id automáticamente

Intentar duplicado:
- Crear permiso para 2026-06-01
- Intentar crear otro para la misma fecha
- Resultado: "Ya existe un permiso para esta fecha"
```

#### Prueba: Editar Permiso

```
Ruta: Gestión Permisos → Editar permiso

Cambios:
1. Cambiar fecha a futura
2. Cambiar tipo de permiso
3. Agregar observación

Validaciones esperadas:
✓ Rechaza fechas pasadas
✓ Rechaza solapamiento con otros permisos
✓ Valida existencia de docente
✓ Valida pertinencia a empresa

Mensaje: "Permiso actualizado correctamente"
```

---

### ✅ 3. Vistas Blade - Editar Asistencia

```
Verificar elementos visuales:

✓ Breadcrumb navegable
✓ Alert con errores si los hay
✓ Alert info con registro original
✓ Selector de docente con ID biométrico
✓ DateTime picker para fecha/hora
✓ Selector dropdown para tipo evento
✓ Selector dropdown para origen
✓ Textarea con contador de caracteres (0/500)
✓ Sección de geolocalización (si existe)
✓ Imagen de evidencia fotográfica con zoom
✓ Información adicional (created_at, ID)
✓ Botones: Volver, Eliminar, Guardar
✓ Estilos responsive (mobile-friendly)
✓ Validación de formulario HTML5

Script JavaScript:
✓ Contador de caracteres funciona en tiempo real
✓ Click en eliminar pide confirmación
```

---

### ✅ 4. Vistas Blade - Editar Permiso

```
Verificar elementos visuales:

✓ Breadcrumb navegable
✓ Alert con errores si los hay
✓ Alert info con ID del permiso
✓ Selector de docente
✓ Date picker con mínimo = hoy
✓ Selector de tipo (permiso/comisión/licencia)
✓ Textarea con contador (0/500)
✓ Alert info con datos del permiso
✓ Card de ayuda con tipos de permisos
✓ Información: fecha, docente, creado hace
✓ Botones: Volver, Eliminar, Guardar
✓ Estilos responsive
✓ Validación HTML5

Script JavaScript:
✓ Contador de caracteres en tiempo real
✓ Botón eliminar con confirmación

Validación visual:
✓ Fechas pasadas en color gris/deshabilitadas
✓ Errores resaltados en rojo
✓ Bootstrap classes correcto
```

---

## 🔍 Pruebas Específicas de Validación

### Test 1: Password Fuerte

```
Intentar crear docente con passwords:

❌ "pass"                   → Error (< 8 chars)
❌ "password"               → Error (sin números)
❌ "PASSWORD123"            → Error (sin minúsculas)
❌ "Password"               → Error (sin números)
✓ "Pass1234"                → Aceptado
✓ "MyPass2025"              → Aceptado
✓ "Test@2026Secure"         → Aceptado
```

### Test 2: Colores Válidos

```
Intentar guardar colores:

❌ "0d6efd"                 → Error (sin #)
❌ "#0d6efg"                → Error (carácter inválido)
❌ "#0d6e"                  → Error (solo 4 dígitos)
✓ "#0d6efd"                 → Aceptado
✓ "#FFFFFF"                 → Aceptado
✓ "#000000"                 → Aceptado
✓ "#abc123"                 → Aceptado
```

### Test 3: Emails

```
Intentar guardar email de soporte:

❌ "usuario"                → Error
❌ "usuario@"               → Error
❌ "@dominio.com"           → Error
✓ "soporte@istae.local"     → Aceptado
✓ "admin@empresa.com"       → Aceptado
```

### Test 4: Fechas de Permiso

```
Intentar crear permiso:

❌ 2026-05-27 (pasada)      → Error (after_or_equal:today)
❌ 2025-12-31 (pasada)      → Error
✓ 2026-05-28 (hoy)          → Aceptado
✓ 2026-06-01 (futura)       → Aceptado
✓ 2026-12-31               → Aceptado
```

---

## 📊 Validación de Base de Datos

```sql
-- Verificar que fields de validación existan

-- Tabla: usuarios
SELECT * FROM usuarios WHERE company_id = 1 AND rol = 'docente';
-- Debe mostrar: company_id, biometric_id, nombre, username, password, rol

-- Tabla: logs
SELECT * FROM logs WHERE company_id = 1 ORDER BY fecha DESC LIMIT 5;
-- Debe mostrar: company_id, usuario_id, fecha, tipo_evento, origen, descripcion

-- Tabla: permisos
SELECT * FROM permisos WHERE company_id = 1 ORDER BY fecha_permiso DESC;
-- Debe mostrar: company_id, user_id, fecha_permiso, tipo, observacion

-- Tabla: company_brandings
SELECT * FROM company_brandings WHERE company_id = 1;
-- Debe mostrar: logo_path, colores (JSON), textos (JSON), tema
```

---

## 🧩 Pruebas de Integración

### Test 1: Flujo Completo de Branding

```
1. Acceder a /admin/configuracion/branding
2. Cargar logo (PNG 200x80)
3. Cambiar 3 colores (#123456, #654321, #abcdef)
4. Cambiar nombre sistema a "Mi Institución"
5. Cambiar tema a "dark"
6. Guardar

Verificar:
✓ Logo aparece en navbar
✓ Colores aplicados en toda la app
✓ Nombre aparece en header y login
✓ Tema oscuro aplicado globalmente
✓ URL /admin/configuracion/branding mantiene datos
```

### Test 2: Flujo Docente - Edición

```
1. Crear docente: Juan Pérez (ID: 5001)
2. Editar docente: cambiar a María López
3. Cambiar password
4. Habilitar acceso puerta
5. Guardar

Verificar:
✓ Cambios se guardan
✓ Password se actualiza correctamente
✓ Acceso puerta habilitado
✓ Intenta login con nuevo nombre: ✓ funciona
```

### Test 3: Flujo Asistencia - Completo

```
1. Registrar log automático (dispositivo)
2. Ver en gestion_asistencia
3. Click editar
4. Cambiar tipo evento a SALIDA
5. Agregar descripción: "Salida normal"
6. Guardar

Verificar:
✓ Datos anteriores se cargan
✓ Cambios se guardan
✓ Aparece en listado con cambios
✓ Hash de logs actualizado
```

### Test 4: Flujo Permiso - Completo

```
1. Crear permiso: docente X, 2026-06-01, Comisión
2. Editar: cambiar a 2026-06-02
3. Intentar crear otro para 2026-06-02
4. Ver error de solapamiento
5. Cambiar a 2026-06-03
6. Guardar exitosamente

Verificar:
✓ Solapamiento detectado
✓ Errores mostrados correctamente
✓ Actualización funciona
```

---

## ⚠️ Casos Edge (Límites)

```
Test: Campos muy largos

Username: 100+ caracteres    → Truncado a 100 o error
Descripción: 500+ caracteres → Rechazado ("max:500")
Nombre: 150+ caracteres      → Truncado/rechazado

Test: Caracteres especiales

Nombre: "Juan's O'Brien"      → ✓ Aceptado (')
Nombre: "María-Rosa López"    → ✓ Aceptado (-)
Nombre: "Juan@Pérez"          → ✗ Rechazado (@)
Username: "juan_perez"        → ✓ Aceptado (_)
Username: "juan-perez.2"      → ✓ Aceptado (.-_)
Username: "juan@perez"        → ✗ Rechazado (@)
```

---

## 🔐 Pruebas de Seguridad

```
1. Company Isolation
   - Crear permiso con docente de otra empresa
   - Resultado: "El docente no pertenece a esta empresa"

2. Timezone
   - Verificar que fechas se guardan en "America/Guayaquil"
   - Comparar con UTC+0

3. SQL Injection
   - Intentar en username: "admin' OR '1'='1"
   - Resultado: Tratado como string literal (Eloquent ORM)

4. XSS Prevention
   - Intentar en descripción: <script>alert('xss')</script>
   - Resultado: Escapado en {{ }} Blade
```

---

## 📝 Reporte de Prueba

Completa este reporte después de las pruebas:

```
Fecha: ___________
Probador: _________
Versión: 2.0.0

BRANDING: ___/10
- Logo upload: ___
- Color picker: ___
- Textos: ___

VALIDACIONES: ___/10
- Password: ___
- Docente: ___
- Permisos: ___

VISTAS: ___/10
- editar_asistencia: ___
- editar_permiso: ___
- Responsive: ___

GENERAL: ___/10

Bugs encontrados:
1. ________________________
2. ________________________
3. ________________________

Comentarios:
_____________________________
```

---

## 🚀 Comandos Útiles para Pruebas

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Ver rutas
php artisan route:list | grep -E "(branding|permiso|asistencia)"

# Crear datos de prueba
php artisan tinker
> $user = User::first();
> $user->company_id
> $user->nombre

# Ver logs
tail -f storage/logs/laravel.log

# Descargar reporte
php artisan migrate:rollback --step=1
php artisan migrate
php artisan db:seed
```

---

**Documento Actualizado**: 28 de Mayo, 2026
**Versión**: 1.0.0
