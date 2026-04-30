<?php

/**
 * Modelo User - Usuarios del sistema de gestion de cisternas.
 * 
 * Soporta cuatro roles de acceso:
 * - Root: Acceso total al sistema
 * - Admin: Gestion de usuarios y cisternas
 * - User: Operaciones CRUD sobre cisternas
 * - Operario: Solo actualizacion de horas de consumo
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Roles disponibles en el sistema.
     */
    public const ROL_ROOT = 'Root';
    public const ROL_ADMIN = 'admin';
    public const ROL_ADMIN_ALT = 'Administrador';
    public const ROL_USER = 'user';
    public const ROL_USER_ALT = 'Usuario';
    public const ROL_OPERARIO = 'operario';
    public const ROL_OPERARIO_ALT = 'Operario';

    /**
     * Campos asignables en asignacion masiva.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'fecha_registro',
    ];

    /**
     * Campos ocultos en serializacion.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dateFormat = 'Ymd H:i:s';

    public function freshTimestamp()
    {
        return now()->format('Ymd H:i:s');
    }

    /**
     * Conversiones automaticas de tipos de datos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_registro' => 'datetime',
        ];
    }

    // ==================== VERIFICACION DE ROLES ====================

    /**
     * Verifica si el usuario es Root.
     *
     * El rol Root tiene acceso total sin restricciones.
     *
     * @return bool True si el rol es Root
     */
    public function isRoot(): bool
    {
        return $this->role === self::ROL_ROOT;
    }

    /**
     * Verifica si el usuario es Administrador.
     *
     * Incluye al rol Root ya que tiene privilegios superiores.
     *
     * @return bool True si el rol es Admin o Root
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROL_ADMIN, self::ROL_ADMIN_ALT], true) 
            || $this->isRoot();
    }

    /**
     * Verifica si el usuario tiene rol Usuario estandar.
     *
     * @return bool True si el rol es User
     */
    public function isUser(): bool
    {
        return in_array($this->role, [self::ROL_USER, self::ROL_USER_ALT], true);
    }

    /**
     * Verifica si el usuario es Operario.
     *
     * Los operarios solo pueden actualizar horas de consumo.
     *
     * @return bool True si el rol es Operario
     */
    public function isOperario(): bool
    {
        return in_array($this->role, [self::ROL_OPERARIO, self::ROL_OPERARIO_ALT], true);
    }

    // ==================== PERMISOS DE ACCESO ====================

    /**
     * Verifica si el usuario puede visualizar datos.
     *
     * Todos los roles autenticados pueden visualizar.
     *
     * @return bool True si tiene permiso de visualizacion
     */
    public function canView(): bool
    {
        return $this->isUser() 
            || $this->isOperario() 
            || $this->isAdmin() 
            || $this->isRoot();
    }

    /**
     * Verifica si el usuario puede crear registros.
     *
     * Solo User, Admin y Root pueden crear.
     *
     * @return bool True si tiene permiso de creacion
     */
    public function canCreate(): bool
    {
        return $this->isUser() 
            || $this->isAdmin() 
            || $this->isRoot();
    }

    /**
     * Verifica si el usuario puede editar registros.
     *
     * Todos excepto operarios tienen permisos de edicion completos.
     *
     * @return bool True si tiene permiso de edicion completo
     */
    public function canEdit(): bool
    {
        return $this->isUser() 
            || $this->isAdmin() 
            || $this->isRoot();
    }

    /**
     * Verifica si el usuario puede eliminar registros.
     *
     * Solo Admin y Root pueden eliminar.
     *
     * @return bool True si tiene permiso de eliminacion
     */
    public function canDelete(): bool
    {
        return $this->isAdmin() || $this->isRoot();
    }

    /**
     * Verifica si el usuario puede realizar importacion masiva.
     *
     * Solo Admin y Root pueden importar.
     *
     * @return bool True si tiene permiso de importacion
     */
    public function canImport(): bool
    {
        return $this->isAdmin() || $this->isRoot();
    }

    /**
     * Verifica si el usuario puede exportar datos.
     *
     * Todos los roles excepto operarios pueden exportar.
     *
     * @return bool True si tiene permiso de exportacion
     */
    public function canExport(): bool
    {
        return $this->isUser() 
            || $this->isAdmin() 
            || $this->isRoot();
    }
}
