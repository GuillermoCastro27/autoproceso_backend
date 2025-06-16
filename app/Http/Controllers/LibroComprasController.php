<?php

namespace App\Http\Controllers;

use App\Models\LibroCompras;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LibroComprasController extends Controller
{
    public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            lc.compra_cab_id AS id,
            TO_CHAR(lc.\"libC_fecha\", 'dd/mm/yyyy') AS fecha,
            COALESCE(lc.\"libC_tipo_nota\", 'N/A') AS tipo_nota,
            lc.\"prov_razonsocial\" AS proveedor,
            lc.\"prov_ruc\" AS ruc,
            lc.\"condicion_pago\" AS condicion_pago,
            lc.\"tip_imp_nom\" AS impuesto,
            lc.\"libC_monto\" AS monto,
            lc.\"libC_cuota\" AS cuota
        FROM libro_compras lc
        WHERE lc.\"libC_fecha\" BETWEEN ? AND ?
        ORDER BY lc.\"libC_fecha\" ASC
    ", [$desde, $hasta]);
}

}
