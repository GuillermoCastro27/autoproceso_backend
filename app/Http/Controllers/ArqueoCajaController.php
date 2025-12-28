<?php

namespace App\Http\Controllers;

use App\Models\ArqueoCaja;
use App\Models\CobrosCab;
use App\Models\AperturaCierreCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArqueoCajaController extends Controller
{
    // ====================================================
    // 📋 LISTAR ARQUEOS
    // ====================================================
    public function read()
    {
        return ArqueoCaja::orderBy('id', 'desc')->get();
    }

    // ====================================================
    // 🧾 GENERAR ARQUEO (PENDIENTE)
    // ====================================================
    public function store(Request $r)
    {
        $r->validate([
            'empresa_id'                => 'required|integer',
            'sucursal_id'               => 'required|integer',
            'apertura_cierre_caja_id'   => 'required|exists:apertura_cierre_caja,id',
            'user_id'                   => 'required|exists:users,id',
            'tipo_arqueo'               => 'required|in:EFECTIVO,CHEQUE,TARJETA,TOTAL'
        ]);

        // 🔒 Verificar que la caja esté ABIERTA
        $apertura = AperturaCierreCaja::find($r->apertura_cierre_caja_id);

        if ($apertura->aper_cier_estado !== 'ABIERTA') {
            return response()->json([
                'mensaje' => 'La caja no está abierta',
                'tipo'    => 'warning'
            ], 400);
        }

        // 🔒 Evitar arqueos duplicados pendientes
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

            // 🔢 Totales iniciales
            $totalEfectivo = 0;
            $totalCheque   = 0;
            $totalTarjeta  = 0;

            // 🔎 Leer SOLO cobros CONFIRMADOS
            $cobros = CobrosCab::where('apertura_cierre_caja_id', $r->apertura_cierre_caja_id)
                ->where('cobro_estado', 'CONFIRMADO')
                ->get();

            foreach ($cobros as $c) {
                switch ($c->forma_cobro_id) {
                    case 1: // EFECTIVO
                        $totalEfectivo += $c->cobro_importe;
                        break;
                    case 2: // CHEQUE
                        $totalCheque += $c->cobro_importe;
                        break;
                    case 3: // TARJETA
                        $totalTarjeta += $c->cobro_importe;
                        break;
                }
            }

            // 🔢 Total general
            $totalGeneral = $totalEfectivo + $totalCheque + $totalTarjeta;

            // 🧾 Crear arqueo
            $arqueo = ArqueoCaja::create([
                'arqueo_nro'              => 'ARQ-' . now()->format('YmdHis'),
                'arqueo_fecha'            => now(),
                'empresa_id'              => $r->empresa_id,
                'sucursal_id'             => $r->sucursal_id,
                'apertura_cierre_caja_id' => $r->apertura_cierre_caja_id,
                'user_id'                 => $r->user_id,
                'tipo_arqueo'             => $r->tipo_arqueo,
                'total_efectivo'          => $totalEfectivo,
                'total_cheque'            => $totalCheque,
                'total_tarjeta'           => $totalTarjeta,
                'total_general'           => $totalGeneral,
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

    // ====================================================
    // ✅ CONFIRMAR ARQUEO
    // ====================================================
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
