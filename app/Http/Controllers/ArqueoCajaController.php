<?php

namespace App\Http\Controllers;

use App\Models\ArqueoCaja;
use App\Models\CobrosCab;
use App\Models\AperturaCierreCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArqueoCajaController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                a.id,
                TO_CHAR(a.arqueo_fecha, 'DD/MM/YYYY HH24:MI:SS') AS arqueo_fecha,
                a.tipo_arqueo,
                a.estado,

                e.emp_razon_social,
                s.suc_razon_social,
                c.caja_descripcion,
                f.fun_nom || ' ' || f.fun_apellido AS usuario,

                -- Totales calculados con subqueries para evitar producto cartesiano
                COALESCE((
                    SELECT SUM(ce.monto_efectivo)
                    FROM cobro_efectivo ce
                    JOIN cobros_cab cc ON cc.id = ce.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0) AS total_efectivo,

                COALESCE((
                    SELECT SUM(ch.monto_cheque)
                    FROM cobros_cheque ch
                    JOIN cobros_cab cc ON cc.id = ch.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0) AS total_cheque,

                COALESCE((
                    SELECT SUM(ct.monto_tarjeta)
                    FROM cobros_tarjeta ct
                    JOIN cobros_cab cc ON cc.id = ct.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0) AS total_tarjeta,

                COALESCE((
                    SELECT SUM(ce.monto_efectivo)
                    FROM cobro_efectivo ce
                    JOIN cobros_cab cc ON cc.id = ce.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0)
                + COALESCE((
                    SELECT SUM(ch.monto_cheque)
                    FROM cobros_cheque ch
                    JOIN cobros_cab cc ON cc.id = ch.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0)
                + COALESCE((
                    SELECT SUM(ct.monto_tarjeta)
                    FROM cobros_tarjeta ct
                    JOIN cobros_cab cc ON cc.id = ct.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = a.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0) AS total_general

            FROM arqueo_caja a
            JOIN apertura_cierre_caja acc ON acc.id = a.apertura_cierre_caja_id
            JOIN empresa e   ON e.id  = acc.empresa_id
            JOIN sucursal s  ON s.id  = acc.sucursal_id
            JOIN caja c      ON c.id  = acc.caja_id
            JOIN funcionario f ON f.id = a.funcionario_id
            ORDER BY a.id DESC
        ");
    }

    public function store(Request $r)
{
    $r->validate([
        'arqueo_fecha'            => 'required|date',
        'apertura_cierre_caja_id' => 'required|exists:apertura_cierre_caja,id',
        'tipo_arqueo'             => 'required|in:EFECTIVO,CHEQUE,TARJETA,TOTAL'
    ]);

    $apertura = AperturaCierreCaja::find($r->apertura_cierre_caja_id);

    if ($apertura->estado !== 'ABIERTA') {
        return response()->json([
            'mensaje' => 'La caja no está abierta',
            'tipo'    => 'warning'
        ], 400);
    }

    $existe = ArqueoCaja::where('apertura_cierre_caja_id', $r->apertura_cierre_caja_id)
        ->where('estado', 'PENDIENTE')
        ->exists();

    if ($existe) {
        return response()->json([
            'mensaje' => 'Ya existe un arqueo pendiente para esta caja',
            'tipo'    => 'warning'
        ], 400);
    }

    DB::beginTransaction();

    try {

        $arqueo = ArqueoCaja::create([
            'arqueo_fecha'            => $r->arqueo_fecha,
            'apertura_cierre_caja_id' => $r->apertura_cierre_caja_id,
            'funcionario_id'          => auth()->user()->funcionario_id,
            'tipo_arqueo'             => $r->tipo_arqueo,
            'estado'                  => 'PENDIENTE'
        ]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Arqueo generado correctamente',
            'tipo'    => 'success',
            'data'    => $arqueo
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al generar arqueo',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function anular($id)
{
    $arqueo = ArqueoCaja::find($id);

    if (!$arqueo) {
        return response()->json([
            'mensaje' => 'Arqueo no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    if ($arqueo->estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden anular arqueos PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    $arqueo->estado = 'ANULADO';
    $arqueo->save();

    return response()->json([
        'mensaje' => 'Arqueo anulado correctamente',
        'tipo'    => 'success'
    ]);
}

    public function confirmar($id)
    {
        $arqueo = ArqueoCaja::find($id);

        if (!$arqueo) {
            return response()->json([
                'mensaje' => 'Arqueo no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($arqueo->estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede confirmar un arqueo en estado PENDIENTE. Estado actual: ' . $arqueo->estado,
                'tipo'    => 'warning'
            ], 400);
        }

        $arqueo->estado = 'CONFIRMADO';
        $arqueo->save();

        return response()->json([
            'mensaje' => 'Arqueo confirmado correctamente',
            'tipo'    => 'success'
        ]);
    }
}