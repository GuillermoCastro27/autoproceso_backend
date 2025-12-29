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
                d.nota_remi_vent_id,
                d.item_id,
                i.item_decripcion,
                d.nota_remi_vent_det_cantidad,
                d.nota_remi_vent_det_precio
            FROM nota_remi_vent_det d
            JOIN items i ON i.id = d.item_id
            WHERE d.nota_remi_vent_id = ?
        ", [$nota_remi_vent_id]);
    }
}
