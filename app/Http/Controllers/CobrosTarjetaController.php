<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobrosTarjetaController extends Controller
{
    public function readByCobro($cobro_id)
    {
        return DB::table('cobros_tarjeta as ct')
            ->leftJoin('entidad_emisora as ee', 'ee.id', '=', 'ct.entidad_emisora_tarjeta_id')
            ->leftJoin('marca_tarjeta as mt', 'mt.id', '=', 'ct.marca_tarjeta_tarjeta_id')
            ->leftJoin('entidad_adherida as ea', 'ea.id', '=', 'ct.entidad_adherida_tarjeta_id')
            ->select(
                'ct.id',
                'ct.cobros_cab_id',
                'ct.nro_tarjeta',
                DB::raw("TO_CHAR(ct.fecha_vencimiento, 'YYYY-MM-DD') AS fecha_venc_tarjeta"),
                'ct.monto_tarjeta',
                'ct.nro_voucher',

                // 🔹 TEXTOS
                'ee.ent_emis_nombre AS entidad_emisora_tarjeta',
                'mt.marca_nombre AS marca_tarjeta_tarjeta',
                'ea.ent_adh_nombre AS entidad_adherida_tarjeta',

                // 🔹 IDS
                'ct.entidad_emisora_tarjeta_id',
                'ct.marca_tarjeta_tarjeta_id AS marca_tarjeta_tarjeta_id',
                'ct.entidad_adherida_tarjeta_id'
            )
            ->where('ct.cobros_cab_id', $cobro_id)
            ->get();
    }
}
