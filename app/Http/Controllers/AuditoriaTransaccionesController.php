<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaTransaccionesController extends Controller
{
    public function read(Request $request)
    {
        $query = DB::table('auditoria_transacciones')
            ->orderByDesc('fecha_hora')
            ->limit(1000);

        if ($request->filled('tabla_nombre')) {
            $query->where('tabla_nombre', $request->tabla_nombre);
        }

        if ($request->filled('operacion')) {
            $query->where('operacion', $request->operacion);
        }

        if ($request->filled('registro_id')) {
            $query->where('registro_id', $request->registro_id);
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_hora', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->hasta);
        }

        return $query->get();
    }

    public function tablas()
    {
        return DB::table('auditoria_transacciones')
            ->select('tabla_nombre')
            ->distinct()
            ->orderBy('tabla_nombre')
            ->pluck('tabla_nombre');
    }
}
