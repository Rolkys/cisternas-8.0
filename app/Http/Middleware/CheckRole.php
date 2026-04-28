<?php

/**
 * Middleware CheckRole - Control de acceso basado en roles.
 * 
 * Valida que el usuario autenticado posea al menos uno de los roles
 * especificados como parametros en la ruta.
 * 
 * Ejemplo de uso en rutas:
 * Route::get('/admin', [AdminController::class, 'index'])->middleware('role:admin,Root');
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mensaje de error por defecto para acceso denegado.
     */
    private const MENSAJE_ACCESO_DENEGADO = 'No tienes permiso para acceder a esta pagina.';

    /**
     * Ruta de redireccion para usuarios no autenticados.
     */
    private const RUTA_LOGIN = '/login';

    /**
     * Codigo HTTP para acceso prohibido.
     */
    private const HTTP_FORBIDDEN = 403;

    /**
     * Maneja la peticion validando los roles requeridos.
     *
     * Si el usuario no esta autenticado, redirige al login.
     * Si el usuario no tiene ninguno de los roles permitidos, aborta con 403.
     *
     * @param Request $request Peticion HTTP entrante
     * @param Closure $next Siguiente middleware en la cadena
     * @param string ...$roles Lista de roles permitidos para el acceso
     * @return Response Respuesta HTTP (redireccion o continuacion)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar autenticacion
        if (!auth()->check()) {
            return redirect(self::RUTA_LOGIN);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        $userRole = auth()->user()->role;
        
        if (!in_array($userRole, $roles, true)) {
            abort(self::HTTP_FORBIDDEN, self::MENSAJE_ACCESO_DENEGADO);
        }

        return $next($request);
    }
}

