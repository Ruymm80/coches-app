<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que protege las rutas del panel de administración: corta
 * la petición con un 403 si el usuario no tiene rol de administrador.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Solo administradores.');
        }

        return $next($request);
    }
}
