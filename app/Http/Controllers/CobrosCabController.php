<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReciboCobro;
use App\Models\AperturaCierreCaja;
use App\Models\CobrosCab;
use App\Models\CobrosDet;

class CobrosCabController extends Controller
{
    public function read(Request $r)
    {
        $desde = $r->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $r->input('hasta', now()->toDateString());

        return DB::select("
            SELECT * FROM v_cobros
            WHERE cobro_fecha_ts::date BETWEEN ? AND ?
            ORDER BY id DESC
        ", [$desde, $hasta]);
    }
public function store(Request $r)
{
    $r->validate([
        'cobro_fecha' => 'required|date',
        'forma_cobro_id' => 'required|integer|exists:forma_cobro,id',
        'clientes_id' => 'required|integer|exists:clientes,id',
        'cobro_importe' => 'required|numeric|min:0.01',
        'apertura_cierre_caja_id' => 'required|integer|exists:apertura_cierre_caja,id',

        // Cabecera
        'numero_documento' => 'nullable|string|max:50',
        'nro_voucher' => 'nullable|string|max:50',
        'portador' => 'nullable|string|max:100',
        'fecha_cobro_diferido' => 'nullable|date',
        'entidad_emisora_id' => 'nullable|integer|exists:entidad_emisora,id',
        'marca_tarjeta_id' => 'nullable|integer|exists:marca_tarjeta,id',
        'entidad_adherida_id' => 'nullable|integer|exists:entidad_adherida,id',
        'cobro_observacion' => 'nullable|string|max:200',

        // Cuotas
        'cuotas' => 'required|array|min:1',

        // EFECTIVO
        'monto_efectivo' => 'nullable|numeric|min:0',

        // Tarjeta
        'monto_tarjeta' => 'nullable|numeric|min:0',
        'entidad_emisora_tarjeta_id' => 'nullable|integer|exists:entidad_emisora,id',
        'marca_tarjeta_tarjeta_id' => 'nullable|integer|exists:marca_tarjeta,id',
        'entidad_adherida_tarjeta_id' => 'nullable|integer|exists:entidad_adherida,id',

        // Cheque
        'monto_cheque' => 'nullable|numeric|min:0',
        'entidad_emisora_cheque_id' => 'nullable|integer|exists:entidad_emisora,id',

        // Transferencia y QR
        'transferencias' => 'nullable|string',
        'qrs'            => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {

        // ==================================================
        // 1) Validar apertura de caja
        // ==================================================
        $acc = DB::table('apertura_cierre_caja')
            ->where('id', $r->apertura_cierre_caja_id)
            ->first();

        if (!$acc || $acc->estado !== 'ABIERTA') {
            throw new \Exception('La caja no está ABIERTA');
        }

        // ==================================================
        // 2) Validar cuotas y obtener venta
        // ==================================================
        $ventaId = null;

        foreach ($r->cuotas as $ctaId) {

            $cta = DB::table('ctas_cobrar')->where('id', $ctaId)->first();

            if (!$cta) {
                throw new \Exception("Cuenta a cobrar no encontrada (ID {$ctaId})");
            }

            if ($cta->cta_cob_estado !== 'PENDIENTE') {
                throw new \Exception("La cuota ID {$ctaId} no está PENDIENTE");
            }

            if ($ventaId === null) {
                $ventaId = $cta->ventas_cab_id;
            } elseif ($ventaId != $cta->ventas_cab_id) {
                throw new \Exception('No se pueden mezclar cuotas de distintas ventas');
            }
        }

        if (!$ventaId) {
            throw new \Exception('No se pudo determinar la venta');
        }

        // ==================================================
        // 3) Validar suma de medios de cobro (BLINDAJE)
        // ==================================================
        $tarjetasVal      = json_decode($r->input('tarjetas',      '[]'), true) ?? [];
        $chequesVal       = json_decode($r->input('cheques',       '[]'), true) ?? [];
        $transferenciasVal = json_decode($r->input('transferencias','[]'), true) ?? [];
        $qrsVal           = json_decode($r->input('qrs',           '[]'), true) ?? [];

        $totalTarjetas      = array_sum(array_column($tarjetasVal,       'monto_tarjeta'));
        $totalCheques       = array_sum(array_column($chequesVal,        'monto_cheque'));
        $totalTransferencias = array_sum(array_column($transferenciasVal, 'monto_transferencia'));
        $totalQrs           = array_sum(array_column($qrsVal,            'monto_qr'));

        $totalMedios  = (float)($r->monto_efectivo ?? 0) + $totalTarjetas + $totalCheques + $totalTransferencias + $totalQrs;
        $cobroImporte = (float)$r->cobro_importe;

        // Medios digitales no pueden superar el importe
        $totalDigital = $totalTarjetas + $totalCheques + $totalTransferencias + $totalQrs;
        if ($totalDigital > $cobroImporte + 0.01) {
            throw new \Exception('La suma de los medios de cobro supera el importe total');
        }
        // El total entregado debe cubrir el importe (el efectivo puede ser mayor → genera vuelto)
        if ($totalMedios < $cobroImporte - 0.01) {
            throw new \Exception('El monto entregado no alcanza para cubrir el importe total');
        }

        // ==================================================
        // 4) Crear cabecera del cobro
        // ==================================================
        $cobroId = DB::table('cobros_cab')->insertGetId([
            'cobro_fecha' => $r->cobro_fecha,
            'cobro_estado' => 'PENDIENTE',
            'cobro_importe' => $r->cobro_importe,
            'cobro_observacion' => $r->cobro_observacion,
            'numero_documento' => $r->numero_documento,
            'forma_cobro_id' => $r->forma_cobro_id,
            'clientes_id' => $r->clientes_id,
            'ventas_cab_id' => $ventaId,
            'funcionario_id' => auth()->user()->funcionario_id,

            'caja_id' => $acc->caja_id,
            'empresa_id' => $acc->empresa_id,
            'sucursal_id' => $acc->sucursal_id,
            'apertura_cierre_caja_id' => $acc->id,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==================================================
        // 5) Generar detalle del cobro desde ventas_det
        // ==================================================
        $detallesVenta = DB::table('ventas_det')
            ->where('ventas_cab_id', $ventaId)
            ->get();

        if ($detallesVenta->isEmpty()) {
            throw new \Exception('La venta no tiene detalle');
        }

        foreach ($detallesVenta as $det) {
            DB::table('cobros_det')->insert([
                'cobros_cab_id' => $cobroId,
                'item_id' => $det->item_id,
                'cob_det_cantidad' => $det->vent_det_cantidad,
                'cob_det_precio' => $det->vent_det_precio,
                'tipo_impuesto_id' => $det->tipo_impuesto_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==================================================
        // 6) Relacionar cobro con cuotas y marcar ASIGNADO
        // ==================================================
        foreach ($r->cuotas as $ctaId) {

            $cta = DB::table('ctas_cobrar')->where('id', $ctaId)->first();

            DB::table('cobros_ctas_cobrar')->insert([
                'cobros_cab_id'  => $cobroId,
                'ctas_cobrar_id' => $cta->id,
                'monto_cobrado'  => $cta->cta_cob_monto,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::table('ctas_cobrar')
                ->where('id', $cta->id)
                ->update([
                    'cta_cob_estado' => 'ASIGNADO',
                    'updated_at'     => now()
                ]);
        }

        // ==================================================
        // 7) Tarjetas (array JSON)
        // ==================================================
        $tarjetas = json_decode($r->input('tarjetas', '[]'), true) ?? [];
        foreach ($tarjetas as $t) {
            if ((float)($t['monto_tarjeta'] ?? 0) > 0) {
                DB::table('cobros_tarjeta')->insert([
                    'cobros_cab_id'              => $cobroId,
                    'entidad_emisora_tarjeta_id' => $t['entidad_emisora_tarjeta_id'] ?? null,
                    'marca_tarjeta_tarjeta_id'   => $t['marca_tarjeta_tarjeta_id']   ?? null,
                    'entidad_adherida_tarjeta_id'=> $t['entidad_adherida_tarjeta_id']?? null,
                    'nro_tarjeta'                => $t['nro_tarjeta']                ?? null,
                    'fecha_vencimiento'          => $t['fecha_venc_tarjeta']          ?? null,
                    'nro_voucher'                => $t['nro_voucher_tarjeta']         ?? null,
                    'monto_tarjeta'              => $t['monto_tarjeta'],
                    'created_at'                 => now(),
                    'updated_at'                 => now(),
                ]);
            }
        }

        // ==================================================
        // 8) Cheques (array JSON)
        // ==================================================
        $cheques = json_decode($r->input('cheques', '[]'), true) ?? [];
        foreach ($cheques as $ch) {
            if ((float)($ch['monto_cheque'] ?? 0) > 0) {
                DB::table('cobros_cheque')->insert([
                    'cobros_cab_id'             => $cobroId,
                    'entidad_emisora_cheque_id' => $ch['entidad_emisora_cheque_id'] ?? null,
                    'nro_cheque'                => $ch['nro_cheque']                ?? null,
                    'fecha_vencimiento'         => $ch['fecha_venc_cheque']         ?? null,
                    'portador'                  => $ch['portador']                  ?? null,
                    'fecha_cobro_diferido'      => $ch['fecha_cobro_diferido']      ?? null,
                    'monto_cheque'              => $ch['monto_cheque'],
                    'estado_cheque'             => 'RECIBIDO',
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
            }
        }

        // ==================================================
        // 9) Transferencias (array JSON)
        // ==================================================
        $transferencias = json_decode($r->input('transferencias', '[]'), true) ?? [];
        foreach ($transferencias as $tr) {
            if ((float)($tr['monto_transferencia'] ?? 0) > 0) {
                DB::table('cobros_transferencia')->insert([
                    'cobros_cab_id'  => $cobroId,
                    'banco_entidad'  => $tr['banco_entidad']  ?? null,
                    'nro_referencia' => $tr['nro_referencia'] ?? null,
                    'monto_transferencia' => $tr['monto_transferencia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // ==================================================
        // 10) QRs (array JSON)
        // ==================================================
        $qrs = json_decode($r->input('qrs', '[]'), true) ?? [];
        foreach ($qrs as $qr) {
            if ((float)($qr['monto_qr'] ?? 0) > 0) {
                DB::table('cobros_qr')->insert([
                    'cobros_cab_id'  => $cobroId,
                    'nro_referencia' => $qr['nro_referencia'] ?? null,
                    'monto_qr'       => $qr['monto_qr'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // ==================================================
        // 11) Detalle EFECTIVO
        // ==================================================
        if ($r->filled('monto_efectivo') && (float)$r->monto_efectivo > 0) {
            DB::table('cobro_efectivo')->insert([
                'cobros_cab_id'  => $cobroId,
                'monto_efectivo' => $r->monto_efectivo,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        DB::commit();

        return response()->json([
            'mensaje' => 'Cobro registrado correctamente',
            'tipo'    => 'success',
            'id'      => $cobroId
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al registrar el cobro',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function update(Request $r, $id)
{
    $cobro = CobrosCab::find($id);

    if (!$cobro) {
        return response()->json([
            'mensaje' => 'Cobro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    // 🔒 Regla de negocio
    if ($cobro->cobro_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden modificar cobros en estado PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    // =========================
    // 🔹 Validación
    // =========================
    $r->validate([
        'cobro_fecha'    => 'required|date',
        'forma_cobro_id' => 'required|integer|exists:forma_cobro,id',

        // Efectivo
        'monto_efectivo' => 'nullable|numeric|min:0',

        // Tarjeta
        'monto_tarjeta'  => 'nullable|numeric|min:0',

        // Cheque
        'monto_cheque'   => 'nullable|numeric|min:0',

        // Transferencia y QR
        'transferencias' => 'nullable|string',
        'qrs'            => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {

        // ==================================================
        // 🔹 Actualizar cabecera
        // ==================================================
        $cobro->update([
            'cobro_fecha'       => $r->cobro_fecha,
            'forma_cobro_id'    => $r->forma_cobro_id,
            'cobro_observacion' => $r->cobro_observacion ?: null,
            'numero_documento'  => $r->numero_documento  ?: null,
        ]);

        // ==================================================
        // 🔹 Validar suma de medios
        // ==================================================
        $tarjetasUpd       = json_decode($r->input('tarjetas',       '[]'), true) ?? [];
        $chequesUpd        = json_decode($r->input('cheques',        '[]'), true) ?? [];
        $transferenciasUpd = json_decode($r->input('transferencias', '[]'), true) ?? [];
        $qrsUpd            = json_decode($r->input('qrs',            '[]'), true) ?? [];

        $totalTarjUpd  = array_sum(array_column($tarjetasUpd,       'monto_tarjeta'));
        $totalCheqUpd  = array_sum(array_column($chequesUpd,        'monto_cheque'));
        $totalTransUpd = array_sum(array_column($transferenciasUpd, 'monto_transferencia'));
        $totalQrUpd    = array_sum(array_column($qrsUpd,            'monto_qr'));

        $totalMedios  = (float)($r->monto_efectivo ?? 0) + $totalTarjUpd + $totalCheqUpd + $totalTransUpd + $totalQrUpd;
        $cobroImporte = (float)$cobro->cobro_importe;

        if ($totalTarjUpd + $totalCheqUpd + $totalTransUpd + $totalQrUpd > $cobroImporte + 0.01) {
            throw new \Exception('La suma de los medios de cobro supera el importe total');
        }
        if ($totalMedios < $cobroImporte - 0.01) {
            throw new \Exception('El monto entregado no alcanza para cubrir el importe total');
        }

        // ==================================================
        // 🔹 TARJETAS — borrar y re-insertar
        // ==================================================
        DB::table('cobros_tarjeta')->where('cobros_cab_id', $id)->delete();
        foreach ($tarjetasUpd as $t) {
            if ((float)($t['monto_tarjeta'] ?? 0) > 0) {
                DB::table('cobros_tarjeta')->insert([
                    'cobros_cab_id'              => $id,
                    'entidad_emisora_tarjeta_id' => $t['entidad_emisora_tarjeta_id'] ?? null,
                    'marca_tarjeta_tarjeta_id'   => $t['marca_tarjeta_tarjeta_id']   ?? null,
                    'entidad_adherida_tarjeta_id'=> $t['entidad_adherida_tarjeta_id']?? null,
                    'nro_tarjeta'                => $t['nro_tarjeta']                ?? null,
                    'fecha_vencimiento'          => $t['fecha_venc_tarjeta']          ?? null,
                    'nro_voucher'                => $t['nro_voucher_tarjeta']         ?? null,
                    'monto_tarjeta'              => $t['monto_tarjeta'],
                    'created_at'                 => now(),
                    'updated_at'                 => now(),
                ]);
            }
        }

        // ==================================================
        // 🔹 CHEQUES — borrar y re-insertar
        // ==================================================
        DB::table('cobros_cheque')->where('cobros_cab_id', $id)->delete();
        foreach ($chequesUpd as $ch) {
            if ((float)($ch['monto_cheque'] ?? 0) > 0) {
                DB::table('cobros_cheque')->insert([
                    'cobros_cab_id'             => $id,
                    'entidad_emisora_cheque_id' => $ch['entidad_emisora_cheque_id'] ?? null,
                    'nro_cheque'                => $ch['nro_cheque']                ?? null,
                    'fecha_vencimiento'         => $ch['fecha_venc_cheque']         ?? null,
                    'portador'                  => $ch['portador']                  ?? null,
                    'fecha_cobro_diferido'      => $ch['fecha_cobro_diferido']      ?? null,
                    'monto_cheque'              => $ch['monto_cheque'],
                    'estado_cheque'             => 'RECIBIDO',
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
            }
        }

        // ==================================================
        // 🔹 TRANSFERENCIAS — borrar y re-insertar
        // ==================================================
        DB::table('cobros_transferencia')->where('cobros_cab_id', $id)->delete();
        foreach ($transferenciasUpd as $tr) {
            if ((float)($tr['monto_transferencia'] ?? 0) > 0) {
                DB::table('cobros_transferencia')->insert([
                    'cobros_cab_id'  => $id,
                    'banco_entidad'  => $tr['banco_entidad']  ?? null,
                    'nro_referencia' => $tr['nro_referencia'] ?? null,
                    'monto_transferencia' => $tr['monto_transferencia'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // ==================================================
        // 🔹 QRs — borrar y re-insertar
        // ==================================================
        DB::table('cobros_qr')->where('cobros_cab_id', $id)->delete();
        foreach ($qrsUpd as $qr) {
            if ((float)($qr['monto_qr'] ?? 0) > 0) {
                DB::table('cobros_qr')->insert([
                    'cobros_cab_id'  => $id,
                    'nro_referencia' => $qr['nro_referencia'] ?? null,
                    'monto_qr'       => $qr['monto_qr'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // ==================================================
        // 🔹 EFECTIVO (DETALLE)
        // ==================================================
        if ((float)($r->monto_efectivo ?? 0) > 0) {

            DB::table('cobro_efectivo')->updateOrInsert(
                ['cobros_cab_id' => $id],
                [
                    'monto_efectivo' => $r->monto_efectivo,
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );

        } else {
            DB::table('cobro_efectivo')->where('cobros_cab_id', $id)->delete();
        }

        DB::commit();

        return response()->json([
            'mensaje'  => 'Cobro actualizado correctamente',
            'tipo'     => 'success',
            'registro' => $cobro
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al actualizar el cobro',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function anular(Request $r, $id)
{
    $cobro = CobrosCab::find($id);

    if (!$cobro) {
        return response()->json([
            'mensaje' => 'Cobro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    // 🔒 Regla clave
    if ($cobro->cobro_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden anular cobros en estado PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    $r->validate([
        'motivo_anulacion' => 'nullable|string|max:200'
    ]);

    DB::beginTransaction();

    try {

        // Revertir ctas_cobrar de ASIGNADO a PENDIENTE
        $cuotaIds = DB::table('cobros_ctas_cobrar')
            ->where('cobros_cab_id', $id)
            ->pluck('ctas_cobrar_id');

        if ($cuotaIds->isNotEmpty()) {
            DB::table('ctas_cobrar')
                ->whereIn('id', $cuotaIds)
                ->where('cta_cob_estado', 'ASIGNADO')
                ->update(['cta_cob_estado' => 'PENDIENTE', 'updated_at' => now()]);
        }

        $cobro->update([
            'cobro_estado'      => 'ANULADO',
            'cobro_observacion' => $r->motivo_anulacion
                ? 'ANULADO: ' . $r->motivo_anulacion
                : 'ANULADO'
        ]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Cobro anulado correctamente',
            'tipo'    => 'success',
            'registro'=> $cobro 
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al anular el cobro',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function confirmar(Request $r, $id)
{
    $cobro = CobrosCab::find($id);

    if (!$cobro) {
        return response()->json([
            'mensaje' => 'Cobro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    // 🔒 Regla de negocio
    if ($cobro->cobro_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden confirmar cobros en estado PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    DB::beginTransaction();

    try {

        // =========================
        // 🔹 CONFIRMAR CABECERA
        // =========================
        $cobro->update([
            'cobro_estado' => 'CONFIRMADO'
        ]);

        // =========================
        // 🔹 MARCAR CUOTAS COMO COBRADAS
        // =========================
        $cuotas = DB::table('cobros_ctas_cobrar')
            ->where('cobros_cab_id', $id)
            ->get();

        foreach ($cuotas as $q) {

            DB::table('ctas_cobrar')
                ->where('id', $q->ctas_cobrar_id)
                ->update([
                    'cta_cob_estado' => 'COBRADA'
                ]);
        }

        // =========================
        // 🔹 MARCAR ORDEN DE SERVICIO COMO TERMINADO
        // =========================
        DB::table('orden_serv_cab')
            ->whereIn('id', function ($q) use ($cobro) {
                $q->select('orden_serv_cab_id')
                  ->from('orden_serv_venta')
                  ->where('ventas_cab_id', $cobro->ventas_cab_id);
            })
            ->update(['ord_serv_estado' => 'terminado', 'updated_at' => now()]);

        DB::commit();

        return response()->json([
            'mensaje' => 'Cobro confirmado correctamente',
            'tipo'    => 'success',
            'registro'=> $cobro 
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al confirmar el cobro',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}


private function datosRecibo($id): ?array
{
    $cobro = DB::selectOne("
        SELECT
            cc.id,
            cc.clientes_id,
            TO_CHAR(cc.cobro_fecha, 'DD/MM/YYYY HH24:MI') AS cobro_fecha,
            cc.cobro_estado,
            cc.cobro_importe,
            COALESCE(cc.cobro_observacion, '')                           AS cobro_observacion,
            COALESCE(cc.numero_documento, '')                            AS numero_documento,
            COALESCE(cc.nro_voucher, '')                                 AS nro_voucher,
            COALESCE(cc.portador, '')                                    AS portador,
            COALESCE(TO_CHAR(cc.fecha_cobro_diferido, 'DD/MM/YYYY'), '') AS fecha_cobro_diferido,

            fc.for_cob_descripcion AS forma_cobro,

            cli.cli_nombre,
            cli.cli_apellido,
            cli.cli_ruc,
            cli.cli_telefono,
            cli.cli_correo,
            cli.cli_direccion,

            e.emp_razon_social,
            e.emp_direccion,
            e.emp_telefono,

            s.suc_razon_social,
            s.suc_direccion,
            s.suc_telefono,

            f.fun_nom || ' ' || f.fun_apellido AS funcionario,
            ca.caja_descripcion                AS caja,

            COALESCE(ce.monto_efectivo, 0) AS monto_efectivo

        FROM cobros_cab cc
        JOIN forma_cobro fc          ON fc.id  = cc.forma_cobro_id
        JOIN clientes cli            ON cli.id = cc.clientes_id
        JOIN empresa e               ON e.id   = cc.empresa_id
        JOIN sucursal s              ON s.id   = cc.sucursal_id
        JOIN funcionario f           ON f.id   = cc.funcionario_id
        JOIN apertura_cierre_caja ac ON ac.id  = cc.apertura_cierre_caja_id
        JOIN caja ca                 ON ca.id  = ac.caja_id
        LEFT JOIN cobro_efectivo ce  ON ce.cobros_cab_id = cc.id
        WHERE cc.id = ?
    ", [$id]);

    if (!$cobro) {
        return null;
    }

    $detalles = DB::select("
        SELECT
            cd.item_id,
            i.item_decripcion,
            ti.tip_imp_nom,
            cd.cob_det_cantidad AS cantidad,
            cd.cob_det_precio   AS precio,
            (cd.cob_det_cantidad * cd.cob_det_precio) AS subtotal
        FROM cobros_det cd
        JOIN items i          ON i.id  = cd.item_id
        JOIN tipo_impuesto ti ON ti.id = cd.tipo_impuesto_id
        WHERE cd.cobros_cab_id = ?
        ORDER BY cd.item_id
    ", [$id]);

    $cuotas = DB::select("
        SELECT
            ccc.monto_cobrado,
            cc2.id           AS cta_cobrar_id,
            cc2.nro_cuota,
            cc2.cta_cob_monto,
            cc2.cta_cob_estado,
            TO_CHAR(cc2.cta_cob_fecha_vencimiento, 'DD/MM/YYYY') AS fecha_vencimiento,
            'VENTA NRO: ' || TO_CHAR(v.id, '0000000') AS venta_nro,
            v.id             AS ventas_cab_id,
            v.vent_cant_cuota,
            v.condicion_pago
        FROM cobros_ctas_cobrar ccc
        JOIN ctas_cobrar cc2 ON cc2.id = ccc.ctas_cobrar_id
        JOIN ventas_cab v    ON v.id   = cc2.ventas_cab_id
        WHERE ccc.cobros_cab_id = ?
        ORDER BY cc2.nro_cuota
    ", [$id]);

    // Resumen del crédito (para recibo tipo CRÉDITO)
    $resumenCredito = null;
    if (!empty($cuotas)) {
        $ventaId = $cuotas[0]->ventas_cab_id;
        $resumenCredito = DB::selectOne("
            SELECT
                COUNT(*)                                                              AS total_cuotas,
                COALESCE(SUM(cc.cta_cob_monto), 0)                                   AS total_financiado,
                COUNT(CASE WHEN cc.cta_cob_estado = 'COBRADA' THEN 1 END)            AS cuotas_cobradas,
                COALESCE(SUM(CASE WHEN cc.cta_cob_estado = 'COBRADA'
                                  THEN cc.cta_cob_monto ELSE 0 END), 0)              AS total_ya_cobrado
            FROM ctas_cobrar cc
            WHERE cc.ventas_cab_id = ?
        ", [$ventaId]);
    }

    $tarjeta        = DB::table('cobros_tarjeta')->where('cobros_cab_id', $id)->get();
    $cheque         = DB::table('cobros_cheque')->where('cobros_cab_id', $id)->get();
    $transferencias = DB::table('cobros_transferencia')->where('cobros_cab_id', $id)->get();
    $qrs            = DB::table('cobros_qr')->where('cobros_cab_id', $id)->get();

    return compact('cobro', 'detalles', 'cuotas', 'resumenCredito', 'tarjeta', 'cheque', 'transferencias', 'qrs');
}

public function imprimir($id)
{
    $data = $this->datosRecibo($id);

    if (!$data) {
        return response()->json(['mensaje' => 'Cobro no encontrado', 'tipo' => 'error'], 404);
    }

    return response()->json([
        'cab'             => $data['cobro'],
        'detalles'        => $data['detalles'],
        'cuotas'          => $data['cuotas'],
        'resumen_credito' => $data['resumenCredito'],
        'tarjeta'         => $data['tarjeta'],
        'cheque'          => $data['cheque'],
        'transferencias'  => $data['transferencias'],
        'qrs'             => $data['qrs'],
    ]);
}

public function enviarRecibo($id)
{
    $data = $this->datosRecibo($id);

    if (!$data) {
        return response()->json(['mensaje' => 'Cobro no encontrado', 'tipo' => 'error'], 404);
    }

    $cobro = $data['cobro'];

    if (empty($cobro->cli_correo)) {
        return response()->json(['mensaje' => 'El cliente no tiene correo registrado', 'tipo' => 'warning']);
    }

    $datos = array_merge((array) $cobro, [
        'detalles'       => $data['detalles'],
        'cuotas'         => $data['cuotas'],
        'tarjeta'        => $data['tarjeta'],
        'cheque'         => $data['cheque'],
        'transferencias' => $data['transferencias'],
        'qrs'            => $data['qrs'],
    ]);

    Mail::to($cobro->cli_correo)->send(new ReciboCobro($datos));

    return response()->json([
        'mensaje' => 'Recibo enviado correctamente a ' . $cobro->cli_correo,
        'tipo'    => 'success',
    ]);
}

public function detalle($id)
{
    $tarjeta = DB::table('cobros_tarjeta as ct')
        ->leftJoin('entidad_emisora as ee', 'ee.id', '=', 'ct.entidad_emisora_tarjeta_id')
        ->leftJoin('marca_tarjeta as mt',   'mt.id', '=', 'ct.marca_tarjeta_tarjeta_id')
        ->leftJoin('entidad_adherida as ea','ea.id', '=', 'ct.entidad_adherida_tarjeta_id')
        ->select(
            'ct.id', 'ct.cobros_cab_id', 'ct.nro_tarjeta',
            DB::raw("TO_CHAR(ct.fecha_vencimiento, 'YYYY-MM-DD') AS fecha_venc_tarjeta"),
            'ct.monto_tarjeta', 'ct.nro_voucher',
            'ee.ent_emis_nombre AS entidad_emisora_tarjeta',
            'mt.marca_nombre    AS marca_tarjeta_tarjeta',
            'ea.ent_adh_nombre  AS entidad_adherida_tarjeta',
            'ct.entidad_emisora_tarjeta_id',
            DB::raw('ct.marca_tarjeta_tarjeta_id AS marca_tarjeta_tarjeta_id'),
            'ct.entidad_adherida_tarjeta_id'
        )
        ->where('ct.cobros_cab_id', $id)
        ->get();

    $cheque = DB::table('cobros_cheque as cc')
        ->leftJoin('entidad_emisora as ee', 'ee.id', '=', 'cc.entidad_emisora_cheque_id')
        ->select(
            'cc.id', 'cc.cobros_cab_id', 'cc.nro_cheque',
            DB::raw("TO_CHAR(cc.fecha_vencimiento, 'YYYY-MM-DD') AS fecha_venc_cheque"),
            'cc.monto_cheque', 'cc.estado_cheque',
            'cc.portador',
            DB::raw("TO_CHAR(cc.fecha_cobro_diferido, 'YYYY-MM-DD') AS fecha_cobro_diferido"),
            'ee.ent_emis_nombre AS entidad_emisora_cheque',
            'ee.id              AS entidad_emisora_cheque_id'
        )
        ->where('cc.cobros_cab_id', $id)
        ->get();

    $detalles = DB::select("
        SELECT cd.*, i.item_decripcion, ti.tip_imp_nom
        FROM cobros_det cd
        JOIN items        i  ON i.id  = cd.item_id
        JOIN tipo_impuesto ti ON ti.id = cd.tipo_impuesto_id
        WHERE cd.cobros_cab_id = ?
    ", [$id]);

    $cuotas = DB::select("
        SELECT ccc.monto_cobrado,
               cc2.id AS cta_cobrar_id,
               cc2.nro_cuota,
               TO_CHAR(cc2.cta_cob_fecha_vencimiento, 'YYYY-MM-DD') AS fecha_vencimiento,
               'VENTA NRO: ' || TO_CHAR(v.id, '0000000') AS venta_nro
        FROM cobros_ctas_cobrar ccc
        JOIN ctas_cobrar cc2 ON cc2.id = ccc.ctas_cobrar_id
        JOIN ventas_cab  v   ON v.id   = cc2.ventas_cab_id
        WHERE ccc.cobros_cab_id = ?
        ORDER BY cc2.nro_cuota
    ", [$id]);

    $transferencias = DB::table('cobros_transferencia')
        ->where('cobros_cab_id', $id)
        ->get();

    $qrs = DB::table('cobros_qr')
        ->where('cobros_cab_id', $id)
        ->get();

    return response()->json(compact('tarjeta', 'cheque', 'transferencias', 'qrs', 'detalles', 'cuotas'));
}

public function ctas($cobro_id)
{
    return DB::select("
        SELECT
            ccc.id,
            ccc.monto_cobrado,

            cc.id              AS cta_cobrar_id,
            cc.nro_cuota,
            TO_CHAR(cc.cta_cob_fecha_vencimiento, 'YYYY-MM-DD') AS fecha_vencimiento,

            'VENTA NRO: ' || TO_CHAR(v.id, '0000000') AS venta_nro

        FROM cobros_ctas_cobrar ccc

        JOIN ctas_cobrar cc
            ON cc.id = ccc.ctas_cobrar_id

        JOIN ventas_cab v
            ON v.id = cc.ventas_cab_id

        WHERE ccc.cobros_cab_id = ?

        ORDER BY cc.nro_cuota
    ", [$cobro_id]);
}
}
