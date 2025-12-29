<?php

namespace App\Http\Controllers;

use App\Models\ArqueoCaja;
use App\Models\CobrosCab;
use App\Models\AperturaCierreCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArqueoCajaController extends Controller
{
   public function read()
{
    return DB::table('arqueo_caja as a')

        // 🔗 Apertura / Cierre
        ->join('apertura_cierre_caja as acc', 'acc.id', '=', 'a.apertura_cierre_caja_id')

        // 🔗 Empresa y Sucursal (DESDE apertura)
        ->join('empresa as e', 'e.id', '=', 'acc.empresa_id')
        ->join('sucursal as s', 's.empresa_id', '=', 'acc.sucursal_id')

        // 🔗 Caja
        ->join('caja as c', 'c.id', '=', 'acc.caja_id')

        // 🔗 Usuario que genera el arqueo
        ->join('users as u', 'u.id', '=', 'a.user_id')

        // 🔗 Cobros confirmados de esa apertura
        ->leftJoin('cobros_cab as cc', function ($join) {
            $join->on('cc.apertura_cierre_caja_id', '=', 'a.apertura_cierre_caja_id')
                 ->where('cc.cobro_estado', 'CONFIRMADO');
        })

        // 🔗 Detalles reales de cobro
        ->leftJoin('cobro_efectivo as ce', 'ce.cobros_cab_id', '=', 'cc.id')
        ->leftJoin('cobros_cheque as ch', 'ch.cobros_cab_id', '=', 'cc.id')
        ->leftJoin('cobros_tarjeta as ct', 'ct.cobros_cab_id', '=', 'cc.id')

        ->select(
            'a.id',

            // 📅 Fecha de arqueo
            DB::raw("TO_CHAR(a.arqueo_fecha, 'DD/MM/YYYY HH24:MI:SS') as arqueo_fecha"),

            // Estado y tipo
            'a.tipo_arqueo',
            'a.estado',

            // 💰 TOTALES REALES (CORRECTOS)
            DB::raw("COALESCE(SUM(ce.monto_efectivo), 0) as total_efectivo"),
            DB::raw("COALESCE(SUM(ch.monto_cheque), 0) as total_cheque"),
            DB::raw("COALESCE(SUM(ct.monto_tarjeta), 0) as total_tarjeta"),

            DB::raw("
                COALESCE(SUM(ce.monto_efectivo), 0)
              + COALESCE(SUM(ch.monto_cheque), 0)
              + COALESCE(SUM(ct.monto_tarjeta), 0)
              as total_general
            "),

            // 📌 Datos generales
            'e.emp_razon_social as emp_razon_social',
            's.suc_razon_social as suc_razon_social',
            'c.caja_descripcion as caja_descripcion',
            'u.name as usuario'
        )

        ->groupBy(
            'a.id',
            'a.arqueo_fecha',
            'a.tipo_arqueo',
            'a.estado',
            'e.emp_razon_social',
            's.suc_razon_social',
            'c.caja_descripcion',
            'u.name'
        )

        ->orderBy('a.id', 'desc')
        ->get();
}

    public function store(Request $r)
{
    $r->validate([
        'arqueo_fecha'            => 'required|date',
        'apertura_cierre_caja_id' => 'required|exists:apertura_cierre_caja,id',
        'user_id'                 => 'required|exists:users,id',
        'tipo_arqueo'             => 'required|in:EFECTIVO,CHEQUE,TARJETA,TOTAL'
    ]);

    $apertura = AperturaCierreCaja::find($r->apertura_cierre_caja_id);

    if ($apertura->estado !== 'ABIERTA') {
        return response()->json([
            'mensaje' => 'La caja no está abierta',
            'tipo'    => 'warning'
        ], 400);
    }

    $existe = ArqueoCaja::where('apertura_cierre_caja_id', $r->apertura_cierre_caja_id)
        ->where('estado', 'PENDIENTE')
        ->exists();

    if ($existe) {
        return response()->json([
            'mensaje' => 'Ya existe un arqueo pendiente para esta caja',
            'tipo'    => 'warning'
        ], 400);
    }

    DB::beginTransaction();

    try {

        $arqueo = ArqueoCaja::create([
            'arqueo_fecha'            => $r->arqueo_fecha,
            'apertura_cierre_caja_id' => $r->apertura_cierre_caja_id,
            'user_id'                 => $r->user_id,
            'tipo_arqueo'             => $r->tipo_arqueo,
            'estado'                  => 'PENDIENTE'
        ]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Arqueo generado correctamente',
            'tipo'    => 'success',
            'data'    => $arqueo
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al generar arqueo',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function anular($id)
{
    $arqueo = ArqueoCaja::find($id);

    if (!$arqueo) {
        return response()->json([
            'mensaje' => 'Arqueo no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    if ($arqueo->estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden anular arqueos PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    $arqueo->estado = 'ANULADO';
    $arqueo->save();

    return response()->json([
        'mensaje' => 'Arqueo anulado correctamente',
        'tipo'    => 'success'
    ]);
}

    public function confirmar($id)
    {
        $arqueo = ArqueoCaja::find($id);

        if (!$arqueo) {
            return response()->json([
                'mensaje' => 'Arqueo no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        if ($arqueo->estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'El arqueo ya fue confirmado',
                'tipo'    => 'warning'
            ], 400);
        }

        $arqueo->estado = 'CONFIRMADO';
        $arqueo->save();

        return response()->json([
            'mensaje' => 'Arqueo confirmado correctamente',
            'tipo'    => 'success'
        ]);
    }
}