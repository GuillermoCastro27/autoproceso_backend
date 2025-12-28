<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobrosDetController extends Controller
{
    public function read($id)
{
    return DB::select("
        SELECT
            cd.*,

            i.item_decripcion,
            i.id,

            ti.tip_imp_nom

        FROM cobros_det cd

        JOIN items i
            ON i.id = cd.item_id

        JOIN tipo_impuesto ti
            ON ti.id = cd.tipo_impuesto_id

        WHERE cd.cobros_cab_id = $id
    ");
}

}
