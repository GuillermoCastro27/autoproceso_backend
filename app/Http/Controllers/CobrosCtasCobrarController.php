<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobrosCtasCobrarController extends Controller
{
    public function listarCtasCobro($cobros_cab_id)
{
    return DB::select("
        SELECT
            ccc.id,
            ccc.monto_cobrado,
            cc.nro_cuota,
            TO_CHAR(cc.cta_cob_fecha_vencimiento, 'YYYY-MM-DD') AS vencimiento,
            'VENTA NRO: ' || TO_CHAR(v.id, '0000000') AS venta_nro
        FROM cobros_ctas_cobrar ccc
        JOIN ctas_cobrar cc ON cc.id = ccc.ctas_cobrar_id
        JOIN ventas_cab v ON v.id = cc.ventas_cab_id
        WHERE ccc.cobros_cab_id = ?
    ", [$cobros_cab_id]);
}
}
