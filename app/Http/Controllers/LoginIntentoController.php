<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginIntentoController extends Controller
{
    public function read(Request $request)
    {
        $query = DB::table('login_intentos')
            ->orderByDesc('created_at')
            ->limit(500);

        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }

        if ($request->filled('login')) {
            $query->where('login', 'ILIKE', '%' . $request->login . '%');
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        return $query->get();
    }

    public function limpiar()
    {
        $eliminados = DB::table('login_intentos')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        return response()->json([
            'mensaje' => "{$eliminados} registro(s) anteriores a 90 días eliminados",
            'tipo'    => 'success'
        ]);
    }
}
