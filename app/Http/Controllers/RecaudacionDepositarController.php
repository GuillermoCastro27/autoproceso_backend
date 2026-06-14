<?php

namespace App\Http\Controllers;

use App\Models\RecaudacionDepositar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecaudacionDepositarController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                rd.id,
                rd.apertura_cierre_caja_id,
                rd.reca_dep_met_pago,
                rd.reca_dep_estado,
                TO_CHAR(rd.reca_dep_fecha, 'DD/MM/YYYY HH24:MI:SS') AS reca_dep_fecha,
                rd.reca_dep_obs,

                -- Datos de la sesión de caja
                acc.caja_id,
                c.caja_descripcion,
                TO_CHAR(acc.fecha_apertura, 'DD/MM/YYYY HH24:MI:SS') AS fecha_apertura,
                TO_CHAR(acc.fecha_cierre,   'DD/MM/YYYY HH24:MI:SS') AS fecha_cierre,
                e.emp_razon_social,
                s.suc_razon_social,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,

                -- Monto calculado desde cobros confirmados de esa sesión
                COALESCE((
                    SELECT SUM(ce.monto_efectivo)
                    FROM cobro_efectivo ce
                    JOIN cobros_cab cc ON cc.id = ce.cobros_cab_id
                    WHERE cc.apertura_cierre_caja_id = rd.apertura_cierre_caja_id
                      AND cc.cobro_estado = 'CONFIRMADO'
                ), 0) AS monto_efectivo

            FROM recaudaciones_depositar rd
            JOIN apertura_cierre_caja acc ON acc.id = rd.apertura_cierre_caja_id
            JOIN caja c      ON c.id  = acc.caja_id
            JOIN empresa e   ON e.id  = acc.empresa_id
            JOIN sucursal s  ON s.id  = acc.sucursal_id
            JOIN funcionario f ON f.id = acc.funcionario_id
            ORDER BY rd.reca_dep_fecha DESC
        ");
    }

    public function depositar(Request $r, $id)
    {
        $rd = RecaudacionDepositar::find($id);

        if (!$rd) {
            return response()->json(['mensaje' => 'Recaudación no encontrada', 'tipo' => 'error'], 404);
        }

        if ($rd->reca_dep_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede depositar una recaudación PENDIENTE.',
                'tipo'    => 'warning'
            ], 409);
        }

        $rd->update([
            'reca_dep_estado' => 'DEPOSITADO',
            'reca_dep_obs'    => $r->reca_dep_obs ?? $rd->reca_dep_obs,
        ]);

        return response()->json([
            'mensaje' => 'Recaudación marcada como DEPOSITADO correctamente.',
            'tipo'    => 'success',
        ]);
    }

    public function anular($id)
    {
        $rd = RecaudacionDepositar::find($id);

        if (!$rd) {
            return response()->json(['mensaje' => 'Recaudación no encontrada', 'tipo' => 'error'], 404);
        }

        if ($rd->reca_dep_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede anular una recaudación PENDIENTE.',
                'tipo'    => 'warning'
            ], 409);
        }

        $rd->update(['reca_dep_estado' => 'ANULADO']);

        return response()->json([
            'mensaje' => 'Recaudación anulada.',
            'tipo'    => 'success',
        ]);
    }
}
