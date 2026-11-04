<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Modules\Chaquetas\Models\ApiToken as Token;
use App\Modules\Chaquetas\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Autenticación de la app: "Authorization: Bearer <token>".
 * Uso: ->middleware('app:admin'), 'app:colegio' o 'app:estudiante'.
 */
class ApiToken
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $token = Token::findPlain($request->bearerToken());
        $owner = $token?->owner();
        $ok = match ($role) {
            'estudiante' => $owner instanceof Student,
            default      => $owner instanceof User && $owner->role === $role,
        };
        if (! $ok) {
            return response()->json(['message' => 'Tu sesión venció. Vuelve a iniciar sesión.'], 401);
        }
        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinutes(10))) {
            $token->forceFill(['last_used_at' => now(), 'expires_at' => now()->addDays(90)])->save();
        }
        if ($owner instanceof User) {
            Auth::setUser($owner);
        }
        $request->attributes->set('api_token', $token);
        $request->attributes->set('api_owner', $owner);

        return $next($request);
    }
}
