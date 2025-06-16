<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthClientRecep
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('cliente_api')->check() || Auth::guard('recepcionista_api')->check()) {
            return $next($request);
        }

        return response()->json(['message' => 'No autorizado'], 401);
    }
}