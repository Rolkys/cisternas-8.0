<?php

/**
 * Rutas de Autenticación - Sistema de Gestión de Cisternas
 * 
 * Define todas las rutas relacionadas con la autenticación de usuarios:
 * - Registro y login (acceso público)
 * - Recuperación de contraseña
 * - Verificación de email
 * - Confirmación de contraseña
 * - Gestión de perfil y logout
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/**
 * Límite de solicitudes para rutas sensibles.
 */
const THROTTLE_LIMIT = '6,1';

// ==================== RUTAS PÚBLICAS (SIN AUTENTICAR) ====================

Route::middleware('guest')->group(function () {

    // ==================== REGISTRO DE USUARIOS ====================
    
    /**
     * Muestra el formulario de registro de nuevos usuarios.
     */
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    /**
     * Procesa el formulario de registro y crea el usuario.
     */
    Route::post('register', [RegisteredUserController::class, 'store']);

    // ==================== INICIO DE SESIÓN ====================
    
    /**
     * Muestra el formulario de login.
     */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    /**
     * Procesa el formulario de login y autentica al usuario.
     */
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ==================== RECUPERACIÓN DE CONTRASEÑA ====================
    
    /**
     * Muestra el formulario para solicitar restablecimiento de contraseña.
     */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    /**
     * Envía el email con el enlace de restablecimiento.
     */
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    /**
     * Muestra el formulario para establecer nueva contraseña.
     */
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    /**
     * Procesa el restablecimiento de contraseña.
     */
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

});

// ==================== RUTAS REQUIEREN AUTENTICACIÓN ====================

Route::middleware('auth')->group(function () {

    // ==================== VERIFICACIÓN DE EMAIL ====================
    
    /**
     * Muestra la notificación para verificar el email.
     */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    /**
     * Verifica el email mediante enlace firmado.
     * Protegido con firma y throttling para prevenir abuso.
     */
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:' . THROTTLE_LIMIT])
        ->name('verification.verify');

    /**
     * Reenvía el email de verificación.
     * Protegido con throttling para prevenir spam.
     */
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:' . THROTTLE_LIMIT)
        ->name('verification.send');

    // ==================== CONFIRMACIÓN DE CONTRASEÑA ====================
    
    /**
     * Muestra el formulario para confirmar contraseña (acciones sensibles).
     */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    /**
     * Procesa la confirmación de contraseña.
     */
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // ==================== GESTIÓN DE CONTRASEÑA ====================
    
    /**
     * Actualiza la contraseña del usuario autenticado.
     */
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // ==================== CIERRE DE SESIÓN ====================
    
    /**
     * Cierra la sesión del usuario y lo redirige.
     */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

});
