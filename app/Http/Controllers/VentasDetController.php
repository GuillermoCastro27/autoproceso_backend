<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasDetController extends Controller
{
    public function read($ventas_cab_id)
    {
        return DB::select("
            SELECT
                vd.ventas_cab_id,
                vd.item_id,

                i.item_decripcion,
                vd.vent_det_cantidad,
                vd.vent_det_precio,

                ti.tip_imp_nom,

                (vd.vent_det_cantidad * vd.vent_det_precio) AS subtotal,

                CASE
                    WHEN ti.tip_imp_nom = 'IVA10'
                        THEN (vd.vent_det_cantidad * vd.vent_det_precio) / 11
                    WHEN ti.tip_imp_nom = 'IVA5'
                        THEN (vd.vent_det_cantidad * vd.vent_det_precio) / 21
                    ELSE 0
                END AS iva

            FROM ventas_det vd
            JOIN items i ON i.id = vd.item_id
            JOIN tipo_impuesto ti ON ti.id = vd.tipo_impuesto_id
            WHERE vd.ventas_cab_id = ?
        ", [$ventas_cab_id]);
    }
}
