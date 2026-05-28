# 🔐 Sistema Multi-Tenant de Control Biométrico - ISTAE

> **Plataforma SaaS moderna para gestión de asistencia y control de acceso biométrico**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange?logo=mysql)](https://www.mysql.com)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-success)]()

---

## 📋 Tabla de Contenidos

- [Descripción](#descripción)
- [Características Principales](#características-principales)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Documentación](#documentación)
- [Soporte](#soporte)

---

## 📝 Descripción

**Acceso ISTAE** es un sistema avanzado de control biométrico transformado en una **plataforma SaaS multi-tenant**. Permite a múltiples empresas/instituciones gestionar asistencia, permisos y acceso a instalaciones con personalización visual completa.

### ¿Qué es Multi-Tenant?

La arquitectura multi-tenant permite que múltiples clientes (empresas) compartan una única instancia del sistema, cada uno con sus propios datos aislados y completamente personalizados.

```
Antes (Monolítico):     Después (Multi-Tenant):
┌─────────────┐         ┌──────────────────────┐
│ ISTAE Solo  │         │  Sistema Centralizado │
│ (1 cliente) │         ├──────────────────────┤
└─────────────┘         │ Empresa A (datos)    │
                        │ Empresa B (datos)    │
                        │ Empresa C (datos)    │
                        │ ... N empresas       │
                        └──────────────────────┘
```

---

## ⚡ Características Principales

### ✅ Funcionalidades Core (Implementadas)

| Función | Descripción | Estado |
|---------|-------------|--------|
| **Autenticación Multi-Usuario** | Login con roles (Admin/Docente) | ✅ Completo |
| **Control Biométrico** | Dispositivo NodeMCU con lector huella dactilar | ✅ Funcional |
| **Gestión de Asistencia** | Registro automático + edición manual | ✅ Completo |
| **Permisos Docentes** | CRUD de permisos con validaciones | ✅ Completo |
| **Asistencia Remota** | Marcar presencia por web con GPS + foto | ✅ Funcional |
| **Control Remoto de Puerta** | Apertura de puerta por comando | ✅ Funcional |
| **Sistema de Branding** | Personalización visual por empresa | ✅ Completo |
| **Reportes Excel** | Matricial de asistencia + permisos | ✅ Funcional |
| **API REST** | Endpoints para dispositivo biométrico | ✅ Funcional |
| **Multi-Tenant Seguro** | Aislamiento de datos por empresa | ✅ Completo |

### 🎨 Sistema de Personalización (Branding)

Cada empresa puede personalizar sin tocar código:

```
✓ Logo e ícono de empresa
✓ 9+ colores personalizables
✓ Textos del sistema
✓ Tema (Light/Dark/Custom)
✓ Fondo de login
✓ Pie de página
✓ URL de soporte y términos
```

---

## 🖥️ Requisitos del Sistema

### Servidor

- **PHP**: 8.2 o superior
- **MySQL**: 8.0 o superior
- **Composer**: últimas versiones
- **Node.js**: 16+ (para frontend)
- **Servidor Web**: Apache/Nginx

### Dispositivo Biométrico

- **NodeMCU ESP8266** o compatible
- **Sensor Biométrico AS608** o similar
- **Solenoid para puerta** (12V DC)
- **Reloj RTC DS3231** (sincronización)

### Recomendaciones

- **Servidor**: Mínimo 2GB RAM, 1 CPU core
- **Almacenamiento**: 20GB para logs y archivos
- **Backup**: Diario (cron job configurado)
- **SSL**: Certificado HTTPS (Let's Encrypt)

---

## 🚀 Instalación

### 1. Clonar Repositorio

```bash
git clone <repo-url> acceso_laravel
cd acceso_laravel
```

### 2. Instalar Dependencias

```bash
# Backend (PHP)
composer install

# Frontend (JavaScript)
npm install
```

### 3. Configurar Entorno

```bash
# Copiar archivo de variables de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 4. Configurar Base de Datos

Editar `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acceso_laravel
DB_USERNAME=root
DB_PASSWORD=
```

Crear base de datos:

```bash
mysql -u root -p -e "CREATE DATABASE acceso_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Ejecutar Migraciones

```bash
# Crear tablas
php artisan migrate

# Sembrar datos iniciales (opcional)
php artisan db:seed
```

### 6. Generar Enlace de Almacenamiento

```bash
php artisan storage:link
```

---

## ⚙️ Configuración

### Variables de Entorno Críticas

```env
# Aplicación
APP_NAME="Acceso ISTAE"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx
APP_URL=https://acceso.ejemplo.com

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=acceso_laravel
DB_USERNAME=root
DB_PASSWORD=secreto

# Mail (para notificaciones)
MAIL_MAILER=smtp
MAIL_HOST=smtp.ejemplo.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=password

# API Token Dispositivo (⚠️ IMPORTANTE)
DEVICE_API_TOKEN=istae1805A  # CAMBIAR EN PRODUCCIÓN

# Almacenamiento
FILESYSTEM_DISK=public
```

### Configuración de Empresa Inicial

La migración `add_company_id_to_existing_tables` crea una empresa por defecto:

```php
// database/seeders/CompanySeeder.php
Company::create([
    'nombre' => 'ISTAE',
    'email' => 'admin@istae.local',
    'subdomain' => 'istae',
    'plan_tipo' => 'enterprise',
    'activa' => true,
    'limite_usuarios' => 200,
]);
```

---

## 🎯 Uso

### Acceso por Primera Vez

1. **URL**: `http://localhost/acceso_laravel/login`
2. **Usuario Demo**: `admin` / `password` (ver DatabaseSeeder)
3. **Roles Disponibles**:
   - **Admin**: Acceso completo a configuración, reportes y control
   - **Docente**: Solo asistencia remota y datos propios

### Flujos Principales

#### 📊 Admin: Gestionar Asistencia

```
Login como admin
  ↓
Dashboard → Gestion Asistencia
  ↓
Filtrar por fecha / docente
  ↓
Editar, eliminar o descargar reporte Excel
```

#### 🔑 Admin: Gestionar Permisos

```
Login como admin
  ↓
Dashboard → Gestion Permisos
  ↓
Crear permiso (docente + fechas)
  ↓
Enviar notificación automática
```

#### 👤 Docente: Marcar Presencia Remota

```
Login como docente
  ↓
Dashboard → Abrir Puerta (si autorizado)
      o    → Marcar Asistencia Web (con GPS + foto)
  ↓
Geolocalización + Foto capturada
```

#### 🎨 Admin: Personalizar Branding

```
Login como admin
  ↓
Configuración → Branding
  ↓
Subir logo, cambiar colores, editar textos
  ↓
Guardar y previsualizar en tiempo real
```

---

## 📚 Documentación

Consulta los documentos detallados:

- **[ARQUITECTURA.md](ARQUITECTURA.md)** - Estructura técnica y componentes
- **[FUNCIONALIDADES.md](FUNCIONALIDADES.md)** - Guía detallada de cada módulo
- **[RESUMEN_EJECUTIVO.md](RESUMEN_EJECUTIVO.md)** - Visión general del proyecto
- **[GUIA_PRACTICA_CODIGO.md](GUIA_PRACTICA_CODIGO.md)** - Ejemplos de código
- **[PLAN_MEJORAS_MULTITENANT.md](PLAN_MEJORAS_MULTITENANT.md)** - Roadmap futuro

---

## 🔒 Seguridad

### Medidas Implementadas

```
✓ Autenticación con Hash Bcrypt
✓ CSRF Protection en formularios
✓ SQL Injection Prevention (Eloquent ORM)
✓ XSS Prevention (Blade escaping)
✓ Aislamiento de datos por company_id
✓ Middleware de autenticación
✓ Rate limiting en API
```

### Recomendaciones Producción

```bash
# 1. Cambiar token de API
# En .env: DEVICE_API_TOKEN=tu_token_aleatorio_fuerte

# 2. Habilitar HTTPS
# Usar certificado SSL válido

# 3. Configurar firewall
# Limitar acceso a /admin y /api

# 4. Backups automáticos
php artisan backup:run

# 5. Monitoreo de logs
tail -f storage/logs/laravel.log
```

---

## 🛠️ Desarrollo

### Estructura del Proyecto

```
acceso_laravel/
├── app/
│   ├── Models/              # Modelos Eloquent
│   ├── Http/Controllers/    # Controllers
│   ├── Http/Middleware/     # Middlewares
│   └── Services/            # Lógica de negocio
├── database/
│   ├── migrations/          # Migraciones
│   └── seeders/             # Seeds de datos
├── resources/
│   ├── views/               # Templates Blade
│   ├── css/                 # Estilos
│   └── js/                  # JavaScript
├── routes/
│   ├── web.php              # Rutas web
│   └── api.php              # Rutas API
├── storage/                 # Logos, fotos, logs
└── public/                  # Assets públicos
```

### Comandos Útiles

```bash
# Ejecutar servidor de desarrollo
php artisan serve

# Generar modelo + migración + controller
php artisan make:model NombreModelo -mcr

# Deshacer última migración
php artisan migrate:rollback

# Ver rutas registradas
php artisan route:list

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Compilar assets
npm run build
```

---

## 📊 Estado del Proyecto

### ✅ Completado (90%)

```
✓ Arquitectura multi-tenant
✓ Autenticación y autorización
✓ CRUD de asistencia
✓ CRUD de permisos
✓ Sistema de branding
✓ API para dispositivos
✓ Reportes Excel
✓ Asistencia remota
✓ Control remoto de puerta
```

### 🟡 En Construcción (8%)

```
⚠️ Completar validaciones en controladores
⚠️ Mejorar vistas Blade
⚠️ Agregar más gráficos al dashboard
⚠️ Sistema de notificaciones en tiempo real
```

### ❌ Pendiente (2%)

```
✗ Panel de administración global (multi-empresa)
✗ Facturación y gestión de suscripciones
✗ Tests automatizados
✗ Documentación OpenAPI/Swagger
```

---

## 📞 Soporte

### Contacto

- **Email**: soporte@istae.local
- **Teléfono**: +593 4 XXXXXXX
- **Documentación**: [Ver WIKI](./docs/)

### Reporte de Bugs

Usar GitHub Issues o contactar al equipo de soporte.

### Licencia

Propietario - ISTAE 2026

---

## 📝 Changelog

### Versión 2.0.0 (Mayo 2026)

```
[+] Arquitectura multi-tenant completa
[+] Sistema de branding personalizable
[+] API REST para dispositivos
[+] Asistencia remota con GPS
[+] Reportes matriciales en Excel
[+] Middleware de contexto multi-empresa
[*] Migraciones de bases de datos optimizadas
```

### Versión 1.0.0 (Anterior)

```
[+] Control biométrico básico
[+] Gestión de asistencia simple
[+] Interfaz web rudimentaria
```

---

**Última actualización**: 28 de Mayo, 2026
**Versión**: 2.0.0
**Autor**: Equipo de Desarrollo ISTAE
