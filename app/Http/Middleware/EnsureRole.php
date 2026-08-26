<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Restringe el acceso según el rol del usuario autenticado.
     * Uso: ->middleware('role:admin') o ->middleware('role:colegio')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (! $user || ($user->role ?? 'admin') !== $role) {
            Auth::logout();

            $loginRoute = $role === 'colegio' ? 'portal.login' : 'admin.login';

            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Inicia sesión con una cuenta autorizada.']);
        }

        return $next($request);
    }
}
