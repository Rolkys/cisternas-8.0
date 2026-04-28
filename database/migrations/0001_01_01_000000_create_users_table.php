<?php

/**
 * Migración inicial de usuarios y tablas de autenticación
 * 
 * Crea la estructura base para el sistema de gestión de usuarios,
 * incluyendo campos personalizados para roles y control de acceso.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración creando las tablas necesarias.
     */
    public function up(): void
    {
        // ==================== TABLA DE USUARIOS ====================
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre completo del usuario');
            $table->string('email')->unique()->comment('Email único para autenticación');
            $table->timestamp('email_verified_at')->nullable()->comment('Fecha de verificación del email');
            $table->string('password')->comment('Contraseña hasheada');
            
            // Campos personalizados del sistema
            $table->string('role')->default('Usuario')->comment('Rol del usuario: Root, Administrador, Usuario, operario');
            $table->boolean('is_active')->default(true)->comment('Estado de activación del usuario');
            $table->timestamp('fecha_registro')->nullable()->comment('Fecha de registro en el sistema');
            
            $table->rememberToken();
            $table->timestamps();
        });

        // ==================== TABLA DE TOKENS DE RESET DE CONTRASEÑA ====================
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Email del usuario solicitante');
            $table->string('token')->comment('Token único para reset de contraseña');
            $table->timestamp('created_at')->nullable()->comment('Fecha de creación del token');
        });

        // ==================== TABLA DE SESIONES ====================
        
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('ID único de sesión');
            $table->foreignId('user_id')->nullable()->index()->comment('ID del usuario asociado');
            $table->string('ip_address', 45)->nullable()->comment('Dirección IP del cliente');
            $table->text('user_agent')->nullable()->comment('Navegador/Cliente del usuario');
            $table->longText('payload')->comment('Datos serializados de la sesión');
            $table->integer('last_activity')->index()->comment('Timestamp de última actividad');
        });
    }

    /**
     * Revierte la migración eliminando las tablas creadas.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
