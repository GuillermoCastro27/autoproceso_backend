<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Accesos;

class CheckPermiso
{
    public function handle(Request $request, Closure $next, string $modulo)
    {
        $user = $request->user();

        if (!$user->perfil_id) {
            return response()->json([
                'mensaje' => 'El usuario no tiene un perfil asignado',
                'tipo'    => 'error'
            ], 403);
        }

        $tieneAcceso = Accesos::where('perfil_id', $user->perfil_id)
            ->where('acc_estado', 'ACTIVO')
            ->whereHas('modulo', fn($q) => $q->where('mod_nombre', $modulo))
            ->exists();

        if (!$tieneAcceso) {
            return response()->json([
                'mensaje' => 'No tiene permiso para acceder a este recurso',
                'tipo'    => 'error'
            ], 403);
        }

        return $next($request);
    }
}
