<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaRemiVentDetController extends Controller
{
    public function read($nota_remi_vent_id)
    {
        return DB::select("
            SELECT
                d.id,
                d.nota_remi_vent_id,
                d.item_id,
                i.item_decripcion,
                d.nota_remi_vent_det_cantidad,
                d.nota_remi_vent_det_precio,
                d.tipo_origen,
                d.orden_serv_cab_id,
                CASE
                    WHEN d.orden_serv_cab_id IS NOT NULL
                    THEN 'Servicio — ORDEN Nº ' || LPAD(d.orden_serv_cab_id::text, 7, '0')
                    ELSE 'Venta'
                END AS origen_label,
                ROUND((d.nota_remi_vent_det_cantidad * d.nota_remi_vent_det_precio)::numeric, 2) AS subtotal
            FROM nota_remi_vent_det d
            JOIN items i ON i.id = d.item_id
            WHERE d.nota_remi_vent_id = ?
            ORDER BY d.tipo_origen DESC, d.id ASC
        ", [$nota_remi_vent_id]);
    }
}
