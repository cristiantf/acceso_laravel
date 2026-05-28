<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocenteController;

Route::get('/', function () {
    return redirect('/login');
})->name('index');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/actualizar_password', [AuthController::class, 'updatePassword'])->name('actualizar_password');
    Route::get('/perfil', [AuthController::class, 'perfil'])->name('perfil');

    // Admin Routes
    Route::prefix('admin')->middleware('can:admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin_dashboard');
        Route::get('/abrir', [AdminController::class, 'abrirPuerta'])->name('admin_abrir');
        Route::post('/sincronizar_hora', [AdminController::class, 'sincronizarHora'])->name('admin_sincronizar_hora');
        
        Route::get('/gestion_asistencia', [AdminController::class, 'gestionAsistencia'])->name('gestion_asistencia');
        Route::get('/gestion_permisos', [AdminController::class, 'gestionPermisos'])->name('gestion_permisos');
        
        Route::get('/asistencia/editar/{id}', [AdminController::class, 'editarAsistencia'])->name('editar_asistencia');
        Route::post('/asistencia/actualizar', [AdminController::class, 'actualizarAsistencia'])->name('actualizar_asistencia');
        Route::get('/asistencia/eliminar/{id}', [AdminController::class, 'eliminarAsistencia'])->name('eliminar_asistencia');
        
        Route::post('/permiso/crear', [AdminController::class, 'crearPermiso'])->name('crear_permiso');
        Route::get('/permiso/editar/{id}', [AdminController::class, 'editarPermiso'])->name('editar_permiso');
        Route::post('/permiso/actualizar', [AdminController::class, 'actualizarPermiso'])->name('actualizar_permiso');
        Route::get('/permiso/eliminar/{id}', [AdminController::class, 'eliminarPermiso'])->name('eliminar_permiso');
        
        Route::get('/api/logs_admin', [AdminController::class, 'getLogsJson']);

        Route::get('/reporte_matricial', [AdminController::class, 'descargarReporteMatricial'])->name('descargar_reporte_matricial');
        Route::get('/reporte_permisos', [AdminController::class, 'descargarReportePermisos'])->name('descargar_reporte_permisos');
        
        // Rutas de Configuración y Branding
        Route::prefix('configuracion')->group(function () {
            Route::get('/branding', [\App\Http\Controllers\Admin\BrandingController::class, 'show'])->name('admin.branding.show');
            Route::post('/branding', [\App\Http\Controllers\Admin\BrandingController::class, 'update'])->name('admin.branding.update');
            Route::post('/branding/logo', [\App\Http\Controllers\Admin\BrandingController::class, 'subirLogo'])->name('admin.branding.upload-logo');
        });
    });
    
    Route::middleware('can:admin')->group(function() {
        Route::post('/crear_docente', [AdminController::class, 'crearDocente'])->name('crear_docente');
        Route::get('/editar_docente/{id}', [AdminController::class, 'editarDocente'])->name('editar_docente');
        Route::post('/actualizar_docente', [AdminController::class, 'actualizarDocente'])->name('actualizar_docente');
        Route::get('/eliminar_docente/{id}', [AdminController::class, 'eliminarDocente'])->name('eliminar_docente');
    });

    // Docente Routes
    Route::prefix('docente')->group(function () {
        Route::get('/dashboard', [DocenteController::class, 'dashboard'])->name('docente_dashboard');
        Route::get('/abrir_puerta', [DocenteController::class, 'abrirPuerta'])->name('docente_abrir');
        Route::post('/marcar_web', [DocenteController::class, 'marcarWeb'])->name('docente_marcar');
    });
});
