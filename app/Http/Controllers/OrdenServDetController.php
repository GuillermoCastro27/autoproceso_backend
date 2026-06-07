<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenServDetController extends Controller
{
    public function read($id)
    {
        return DB::select("
            SELECT
                osd.orden_serv_cab_id,
                osd.item_id,
                osd.orden_serv_det_cantidad,
                osd.orden_serv_det_costo,
                osd.orden_serv_det_cantidad_stock,
                osd.tipo_impuesto_id,
                osd.marca_id,
                osd.modelo_id,
                i.item_decripcion,
                ti.tip_imp_nom,
                ma.marc_nom,
                mo.modelo_nom
            FROM orden_serv_det osd
            JOIN items         i  ON i.id  = osd.item_id
            JOIN tipo_impuesto ti ON ti.id = osd.tipo_impuesto_id
            LEFT JOIN marca   ma  ON ma.id = osd.marca_id
            LEFT JOIN modelo  mo  ON mo.id = osd.modelo_id
            WHERE osd.orden_serv_cab_id = ?
        ", [$id]);
    }
}
