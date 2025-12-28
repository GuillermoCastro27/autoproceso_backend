<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CtasCobrarController extends Controller
{
    public function buscarPorCliente($cliente_id)
    {
        return DB::select("
            SELECT
                cc.id,
                cc.ventas_cab_id,
                cc.nro_cuota,
                cc.cta_cob_monto,
                TO_CHAR(cc.cta_cob_fecha_vencimiento, 'YYYY-MM-DD') AS fecha_vencimiento,
                cc.cta_cob_estado,
                cc.condicion_pago,

                -- Cliente
                c.id AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,

                -- Venta
                'VENTA NRO: ' || TO_CHAR(v.id, '0000000') AS venta_nro,
                TO_CHAR(v.vent_fecha, 'YYYY-MM-DD') AS fecha_venta

            FROM ctas_cobrar cc

            JOIN ventas_cab v
                ON v.id = cc.ventas_cab_id

            JOIN clientes c
                ON c.id = v.clientes_id

            WHERE
                v.clientes_id = ?
                AND cc.cta_cob_estado = 'PENDIENTE'

            ORDER BY cc.cta_cob_fecha_vencimiento ASC
        ", [$cliente_id]);
    }
}
