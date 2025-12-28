<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class CobrosChequeController extends Controller
{
    public function readByCobro($cobro_id)
{
    return DB::table('cobros_cheque as cc')
        ->leftJoin(
            'entidad_emisora as ee',
            'ee.id',
            '=',
            'cc.entidad_emisora_cheque_id'
        )
        ->select(
            'cc.id',
            'cc.cobros_cab_id',
            'cc.nro_cheque',
            DB::raw("TO_CHAR(cc.fecha_vencimiento, 'YYYY-MM-DD') AS fecha_venc_cheque"),
            'cc.monto_cheque',
            'cc.estado_cheque',

            // 🔹 Datos de la entidad emisora
            'ee.ent_emis_nombre AS entidad_emisora_cheque',
            'ee.id AS entidad_emisora_cheque_id'
        )
        ->where('cc.cobros_cab_id', $cobro_id)
        ->get();
}

}
