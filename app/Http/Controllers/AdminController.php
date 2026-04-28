<?php

/**
 * AdminController - Gestión de Usuarios del Sistema
 * 
 * Controlador dedicado a la administración completa de usuarios del sistema.
 * Permite crear, editar, eliminar y gestionar roles y estados de usuarios.
 * Solo accesible por usuarios con rol Root o Administrador.
 */

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Email del usuario root protegido contra modificaciones.
     */
    private const EMAIL_ROOT_PROTEGIDO = 'root@local.es';
    
    /**
     * Roles disponibles en el sistema.
     */
    private const ROLES_DISPONIBLES = ['Root', 'Administrador', 'Usuario', 'operario'];

    /**
     * Muestra el listado de todos los usuarios del sistema.
     *
     * @return \Illuminate\View\View Vista con el listado de usuarios ordenados por fecha de registro
     */
    public function index()
    {
        $users = User::orderByDesc('fecha_registro')->get();

        return view('admin.users', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\View\View Vista del formulario con roles disponibles
     */
    public function create()
    {
        return view('admin.create', [
            'rolesDisponibles' => self::ROLES_DISPONIBLES
        ]);
    }

    /**
     * Crea un nuevo usuario en el sistema.
     *
     * Valida los datos del formulario, genera la contraseña determinística
     * y crea el usuario con los datos proporcionados.
     *
     * @param Request $request Datos del formulario de creación
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password_generada' => 'required|string|min:3',
            'role' => ['required', Rule::in(self::ROLES_DISPONIBLES)],
        ]);

        $plainPassword = $this->generatePasswordFromEmail($validated['email']);
        
        if ($validated['password_generada'] !== $plainPassword) {
            return back()
                ->withErrors(['password_generada' => 'Debes generar la contraseña con el botón antes de crear.'])
                ->withInput();
        }

        $this->crearUsuario($validated['email'], $validated['role'], $plainPassword);
        $this->logCreacionUsuario($validated['email']);

        return redirect()->route('admin.users')->with(
            'success',
            "Usuario creado correctamente.<br>Contraseña generada: <b>{$plainPassword}</b>"
        );
    }

    /**
     * Cambia el estado de activación de un usuario.
     *
     * Alterna entre activado/desactivado. Protege al usuario root.
     *
     * @param User $user Usuario a modificar
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de estado
     */
    public function toggle(User $user)
    {
        if ($this->esUsuarioRootProtegido($user)) {
            return back()->with('error', 'No se puede modificar el estado del usuario root.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $message = $user->is_active
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado correctamente.';

        return back()->with('success', $message);
    }

    /**
     * Actualiza el rol de un usuario específico.
     *
     * Permite cambiar el rol del usuario excepto si es el usuario root.
     *
     * @param Request $request Datos del formulario con nuevo rol
     * @param User $user Usuario a modificar
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de confirmación
     */
    public function changeRole(Request $request, User $user)
    {
        if ($this->esUsuarioRootProtegido($user)) {
            return back()->with('error', 'No se puede modificar el rol del usuario root.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES_DISPONIBLES)],
        ]);

        $user->role = $validated['role'];
        $user->save();

        return back()->with('success', "Rol actualizado a {$validated['role']} para {$user->email}.");
    }

    /**
     * Elimina un usuario del sistema.
     *
     * Elimina permanentemente el usuario excepto si es el usuario root.
     *
     * @param User $user Usuario a eliminar
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de confirmación
     */
    public function destroy(User $user)
    {
        if ($this->esUsuarioRootProtegido($user)) {
            return back()->with('error', 'No se puede eliminar el usuario root.');
        }

        $email = $user->email;
        $user->delete();

        return back()->with('success', "Usuario {$email} eliminado correctamente.");
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     *
     * @param User $user Usuario a editar
     * @return \Illuminate\View\View Vista del formulario con datos del usuario y roles disponibles
     */
    public function edit(User $user)
    {
        return view('admin.edit', [
            'user' => $user,
            'rolesDisponibles' => self::ROLES_DISPONIBLES
        ]);
    }

    /**
     * Muestra los detalles completos de un usuario.
     *
     * Incluye contraseña generada y capacidades según su rol.
     *
     * @param User $user Usuario a visualizar
     * @return \Illuminate\View\View Vista de detalles con información completa
     */
    public function show(User $user)
    {
        $generatedPassword = $this->generatePasswordFromEmail($user->email);
        $capabilities = $this->getRoleCapabilities($user->role);

        return view('admin.show', compact('user', 'generatedPassword', 'capabilities'));
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * Permite modificar rol y estado de activación.
     *
     * @param Request $request Datos del formulario
     * @param User $user Usuario a actualizar
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de confirmación
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES_DISPONIBLES)],
            'is_active' => 'nullable|boolean',
        ]);

        $user->role = $validated['role'];
        $user->is_active = $request->boolean('is_active');
        $user->save();

        return redirect()->route('admin.users')
            ->with('success', "Usuario {$user->email} actualizado correctamente.");
    }

    // ==================== MÉTODOS PRIVADOS AUXILIARES ====================

    /**
     * Verifica si el usuario es el root protegido.
     *
     * @param User $user Usuario a verificar
     * @return bool True si es el usuario root protegido
     */
    private function esUsuarioRootProtegido(User $user): bool
    {
        return $user->email === self::EMAIL_ROOT_PROTEGIDO;
    }

    /**
     * Crea un nuevo usuario con los datos proporcionados.
     *
     * @param string $email Email del usuario
     * @param string $role Rol asignado
     * @param string $password Contraseña en texto plano
     * @return User Usuario creado
     */
    private function crearUsuario(string $email, string $role, string $password): User
    {
        return User::create([
            'name' => explode('@', $email)[0],
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
            'fecha_registro' => now(),
        ]);
    }

    /**
     * Registra en el log la creación de un usuario.
     *
     * @param string $userEmail Email del usuario creado
     */
    private function logCreacionUsuario(string $userEmail): void
    {
        Log::info('Usuario creado por administrador', [
            'user_email' => $userEmail,
            'admin_email' => auth()->user()?->email,
        ]);
    }

    /**
     * Genera una contraseña determinística a partir del email.
     *
     * Algoritmo: parte_local_en_mayúsculas + primer_carácter_ascii + último_carácter_ascii
     *
     * @param string $email Email del usuario
     * @return string Contraseña generada
     * @throws \InvalidArgumentException Si el email no es válido
     */
    private function generatePasswordFromEmail(string $email): string
    {
        $localPart = trim(explode('@', $email)[0] ?? '');
        if ($localPart === '') {
            throw new \InvalidArgumentException('El email no tiene parte local válida.');
        }

        $upperLocal = strtoupper($localPart);
        $firstChar = $upperLocal[0];
        $lastChar = $upperLocal[strlen($upperLocal) - 1];

        return $upperLocal . ord($firstChar) . ord($lastChar);
    }

    /**
     * Obtiene la descripción de capacidades según el rol.
     *
     * @param string $role Rol del usuario
     * @return array<string> Lista de capacidades descriptivas
     */
    private function getRoleCapabilities(string $role): array
    {
        return match ($role) {
            'Root' => [
                'Gestión total del sistema.',
                'Puede crear, editar y eliminar usuarios.',
                'Puede gestionar todas las cisternas y operaciones.',
            ],
            'Administrador' => [
                'Gestión de usuarios (excepto restricciones del root).',
                'Puede crear, editar y eliminar registros de negocio.',
                'Acceso completo a paneles de administración.',
            ],
            'operario' => [
                'Puede consultar información operativa.',
                'Puede registrar o actualizar datos operativos permitidos.',
                'No puede gestionar usuarios administradores/root.',
            ],
            default => [
                'Puede ver la información permitida por su perfil.',
                'Puede operar sobre funciones básicas habilitadas.',
                'No puede administrar usuarios del sistema.',
            ],
        };
    }
}


