<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AperturaCierreCaja;
use Illuminate\Support\Facades\DB;

class AperturaCierreCajaController extends Controller
{
   public function read()
{
    return DB::table('apertura_cierre_caja as acc')
        ->join('empresa as e', 'e.id', '=', 'acc.empresa_id')
        ->join('sucursal as s', 's.empresa_id', '=', 'acc.sucursal_id')
        ->join('caja as c', 'c.id', '=', 'acc.caja_id')
        ->join('users as u', 'u.id', '=', 'acc.user_id')
        ->select(
            'acc.id',

            // Apertura
            DB::raw("TO_CHAR(acc.fecha_apertura, 'DD/MM/YYYY HH24:MI:SS') as fecha_apertura"),
            DB::raw("COALESCE(acc.monto_apertura, 0) as monto_apertura"),

            // Cierre (puede venir null)
            DB::raw("
                CASE 
                    WHEN acc.fecha_cierre IS NOT NULL 
                    THEN TO_CHAR(acc.fecha_cierre, 'DD/MM/YYYY HH24:MI:SS')
                    ELSE ''
                END as fecha_cierre
            "),

            // Totales de cierre
            DB::raw("COALESCE(acc.monto_efectivo_cierre, 0) as monto_efectivo_cierre"),
            DB::raw("COALESCE(acc.monto_tarjeta_cierre, 0) as monto_tarjeta_cierre"),
            DB::raw("COALESCE(acc.monto_cheque_cierre, 0) as monto_cheque_cierre"),

            // Estado
            'acc.estado',

            // Datos generales
            'e.emp_razon_social as emp_razon_social',
            's.suc_razon_social as suc_razon_social',
            'c.caja_descripcion as caja_descripcion',
            'u.name as usuario'
        )
        ->orderBy('acc.fecha_apertura', 'desc')
        ->get();
}
    public function store(Request $request)
{
    $request->validate([
        'empresa_id'      => 'required|integer',
        'sucursal_id'     => 'required|integer',
        'caja_id'         => 'required|integer',
        'user_id'         => 'required|integer',
        'fecha_apertura'  => 'required|date',
        'monto_apertura'  => 'required|numeric|min:0'
    ]);

    $existeCajaAbierta = AperturaCierreCaja::where('empresa_id', $request->empresa_id)
        ->where('sucursal_id', $request->sucursal_id)
        ->where('caja_id', $request->caja_id)
        ->where('estado', 'ABIERTA')
        ->exists();

    if ($existeCajaAbierta) {
        return response()->json([
            'mensaje' => 'Ya existe una caja ABIERTA para esta caja y sucursal.',
            'tipo'    => 'warning'
        ], 409);
    }

    $apertura = AperturaCierreCaja::create([
        'empresa_id'      => $request->empresa_id,
        'sucursal_id'     => $request->sucursal_id,
        'caja_id'         => $request->caja_id,
        'user_id'         => $request->user_id,
        'fecha_apertura' => $request->fecha_apertura,
        'monto_apertura' => $request->monto_apertura,
        'estado'          => 'ABIERTA'
    ]);

    return response()->json([
        'mensaje' => 'Caja abierta correctamente.',
        'tipo'    => 'success',
        'data'    => $apertura
    ], 201);
}

    public function anular(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:apertura_cierre_caja,id',
        ]);

        $apertura = AperturaCierreCaja::find($request->id);

        // ❌ Solo se puede anular si está ABIERTA
        if ($apertura->estado !== 'ABIERTA') {
            return response()->json([
                'mensaje' => 'Solo se puede anular una caja que esté ABIERTA.',
                'tipo'    => 'warning',
                'data'    => $apertura
            ], 409);
        }

        // ✅ Anular apertura
        $apertura->estado = 'ANULADA';
        $apertura->save();

        return response()->json([
            'mensaje' => 'Apertura de caja anulada correctamente.',
            'tipo'    => 'success'
        ], 200);
    }
    public function cerrarCaja(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:apertura_cierre_caja,id',
    ]);

    DB::beginTransaction();

    try {

        // 🔒 Bloqueo del registro
        $acc = AperturaCierreCaja::lockForUpdate()->find($request->id);

        if ($acc->estado !== 'ABIERTA') {
            return response()->json([
                'mensaje' => 'Solo se puede cerrar una caja ABIERTA.',
                'tipo'    => 'warning'
            ], 409);
        }

        // ============================
        // 🔹 CALCULAR TOTALES DESDE COBROS CONFIRMADOS
        // ============================

        // EFECTIVO
        $efectivo = DB::table('cobro_efectivo as ce')
            ->join('cobros_cab as cc', 'cc.id', '=', 'ce.cobros_cab_id')
            ->where('cc.apertura_cierre_caja_id', $acc->id)
            ->where('cc.cobro_estado', 'CONFIRMADO')
            ->sum('ce.monto_efectivo');

        // TARJETA
        $tarjeta = DB::table('cobros_tarjeta as ct')
            ->join('cobros_cab as cc', 'cc.id', '=', 'ct.cobros_cab_id')
            ->where('cc.apertura_cierre_caja_id', $acc->id)
            ->where('cc.cobro_estado', 'CONFIRMADO')
            ->sum('ct.monto_tarjeta');

        // CHEQUE
        $cheque = DB::table('cobros_cheque as ch')
            ->join('cobros_cab as cc', 'cc.id', '=', 'ch.cobros_cab_id')
            ->where('cc.apertura_cierre_caja_id', $acc->id)
            ->where('cc.cobro_estado', 'CONFIRMADO')
            ->sum('ch.monto_cheque');

        // ============================
        // 🔹 CERRAR CAJA
        // ============================
        $acc->update([
            'fecha_cierre'          => now(),
            'monto_efectivo_cierre' => $efectivo,
            'monto_tarjeta_cierre'  => $tarjeta,
            'monto_cheque_cierre'   => $cheque,
            'estado'                => 'CERRADA'
        ]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Caja cerrada correctamente.',
            'tipo'    => 'success',
            'totales' => [
                'efectivo' => $efectivo,
                'tarjeta'  => $tarjeta,
                'cheque'   => $cheque
            ]
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al cerrar la caja',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}


    public function buscarAbiertas()
{
    return DB::table('apertura_cierre_caja as acc')
        ->join('caja as c', 'c.id', '=', 'acc.caja_id')
        ->join('users as u', 'u.id', '=', 'acc.user_id')
        ->select(
            'acc.id as apertura_cierre_caja_id',
            'c.caja_descripcion',
            DB::raw("TO_CHAR(acc.fecha_apertura, 'DD/MM/YYYY HH24:MI:SS') as fecha_apertura"),
            'u.name as usuario'
        )
        ->where('acc.estado', 'ABIERTA')
        ->orderBy('acc.fecha_apertura', 'desc')
        ->get();
}
public function buscarAbiertasArqueo()
{
    return DB::table('apertura_cierre_caja as acc')
        ->join('empresa as e', 'e.id', '=', 'acc.empresa_id')
        ->join('sucursal as s', 's.empresa_id', '=', 'acc.sucursal_id')
        ->join('caja as c', 'c.id', '=', 'acc.caja_id')
        ->join('users as u', 'u.id', '=', 'acc.user_id')
        ->select(
            'acc.id as apertura_cierre_caja_id',
            'acc.id as empresa_id',
            'e.emp_razon_social',
            'acc.sucursal_id as sucursal_id',
            's.suc_razon_social',
            'c.caja_descripcion',
            DB::raw("TO_CHAR(acc.fecha_apertura, 'DD/MM/YYYY HH24:MI:SS') as fecha_apertura"),
            'u.name as usuario'
        )
        ->where('acc.estado', 'ABIERTA')
        ->orderBy('acc.id', 'desc')
        ->get();
}

}
