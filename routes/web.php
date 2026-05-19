<?php

/**
 * Rutas Web - Sistema de Gestión de Cisternas
 * 
 * Define todas las rutas de la aplicación con su middleware correspondiente.
 * Las rutas están organizadas por funcionalidad:
 * - Rutas públicas
 * - Rutas de autenticación (requeridas)
 * - Rutas de planificación
 * - Rutas de administración (solo admin/root)
 * - Rutas de cisternas
 * - Rutas de perfil de usuario
 */

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CisternaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlanificacionController;
use Illuminate\Support\Facades\Route;

/**
 * Roles permitidos para administración.
 */
const ROLES_ADMIN = 'Root,Administrador';

// ==================== RUTAS PÚBLICAS ====================

/**
 * Redirige la raíz al listado de cisternas.
 */
Route::get('/', function () {
    return redirect()->route('cisterna.index');
});

// ==================== RUTAS REQUIEREN AUTENTICACIÓN ====================

Route::middleware('auth')->group(function () {

    // ==================== PLANIFICACIÓN ====================
    
    /**
     * Rutas para gestión de planificación temporal.
     * Permite crear, editar, eliminar y exportar planificaciones.
     */
    Route::prefix('planificacion')
        ->name('planificacion.')
        ->group(function () {
            Route::get('/', [PlanificacionController::class, 'index'])->name('index');
            Route::post('/', [PlanificacionController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PlanificacionController::class, 'edit'])->name('edit');
            Route::patch('/{id}', [PlanificacionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PlanificacionController::class, 'destroy'])->name('destroy');
            Route::get('/exportar', [PlanificacionController::class, 'exportar'])->name('exportar');
            Route::delete('/', [PlanificacionController::class, 'clear'])->name('clear');
        });

    // ==================== ADMINISTRACIÓN ====================
    
    /**
     * Rutas de administración - Solo accesible por Root y Administrador.
     * Gestión completa de usuarios del sistema.
     */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:' . ROLES_ADMIN)
        ->group(function () {
            // Listado y CRUD de usuarios
            Route::get('/users', [AdminController::class, 'index'])->name('users');
            Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
            Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
            Route::patch('/users/{user}', [AdminController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
            
            // Acciones especiales sobre usuarios
            Route::patch('/users/{user}/toggle', [AdminController::class, 'toggle'])->name('users.toggle');
            Route::patch('/users/{user}/role', [AdminController::class, 'changeRole'])->name('users.role');
        });

    // ==================== CISTERNAS ====================
    
    /**
     * Rutas principales para gestión de cisternas.
     * Incluye CRUD, importación masiva y exportación.
     */
    Route::prefix('cisterna')->name('cisterna.')->group(function () {
        // Rutas de importación masiva
        Route::get('/bulk-upload', [CisternaController::class, 'bulkUpload'])->name('bulk');
        Route::post('/bulk-upload', [CisternaController::class, 'bulkStore'])->name('bulk.store');
        Route::get('/bulk-confirm', [CisternaController::class, 'bulkConfirm'])->name('bulk.confirm');
        Route::post('/bulk-confirm', [CisternaController::class, 'bulkConfirmStore'])->name('bulk.confirm.store');
        
        // Exportación de datos
        Route::get('/export', [CisternaController::class, 'export'])->name('export');
        
        // Eliminación masiva (rutas duplicadas eliminadas)
        Route::delete('/destroy-all', [CisternaController::class, 'destroyAll'])
            ->middleware('role:Root')
            ->name('destroyAll');
        
        // Actualización de consumo (para operarios)
        Route::patch('/{cisterna}/consumo', [CisternaController::class, 'updateConsumo'])->name('consumo');
    });
    
    // CRUD estándar de cisternas
    Route::resource('cisterna', CisternaController::class);
    
    // Dashboard principal
    Route::get('/dashboard', [CisternaController::class, 'dashboard'])->name('dashboard');

    // ==================== PERFIL DE USUARIO ====================
    
    /**
     * Rutas para gestión del perfil del usuario autenticado.
     */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

});

// ==================== INCLUIR RUTAS DE AUTENTICACIÓN ====================

require __DIR__.'/auth.php';
