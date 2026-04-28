<?php

/**
 * ProfileController - Gestión del Perfil de Usuario
 * 
 * Controlador para la gestión del perfil personal del usuario autenticado.
 * Permite ver, editar datos personales y eliminar la cuenta.
 */

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil del usuario.
     *
     * Presenta el formulario con los datos actuales del usuario
     * para que pueda modificar su información personal.
     *
     * @param Request $request Solicitud HTTP con usuario autenticado
     * @return View Vista del formulario de edición de perfil
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario.
     *
     * Procesa el formulario de edición, actualiza los datos del usuario
     * y maneja la verificación de email si este ha cambiado.
     *
     * @param ProfileUpdateRequest $request Solicitud validada con datos del perfil
     * @return RedirectResponse Redirección al formulario con mensaje de estado
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Actualizar datos validados
        $user->fill($request->validated());

        // Si el email cambió, resetear verificación
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Elimina la cuenta del usuario autenticado.
     *
     * Proceso seguro que requiere confirmación de contraseña,
     * cierra sesión, elimina el usuario y limpia la sesión.
     *
     * @param Request $request Solicitud con contraseña de confirmación
     * @return RedirectResponse Redirección a la página principal
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validar contraseña actual para seguridad
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Cerrar sesión antes de eliminar
        Auth::logout();

        // Eliminar usuario
        $user->delete();

        // Invalidar y regenerar sesión por seguridad
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
