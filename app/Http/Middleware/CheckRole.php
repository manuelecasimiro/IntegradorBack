<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autenticado.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        if (!in_array($user->role, $roles, true) && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar este recurso.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
