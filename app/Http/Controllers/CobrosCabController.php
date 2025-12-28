<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AperturaCierreCaja;
use App\Models\CobrosCab;
use App\Models\CobrosDet;

class CobrosCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT
            cc.id,

            -- =========================
            -- Fechas
            -- =========================
            TO_CHAR(cc.cobro_fecha, 'YYYY-MM-DD HH24:MI:SS') AS cobro_fecha,
            COALESCE(
                TO_CHAR(cc.fecha_cobro_diferido, 'YYYY-MM-DD HH24:MI:SS'),
                'N/A'
            ) AS fecha_cobro_diferido,

            -- =========================
            -- Estado / importes
            -- =========================
            cc.cobro_estado,
            cc.cobro_importe,
            COALESCE(cc.cobro_observacion, '') AS cobro_observacion,

            -- =========================
            -- EFECTIVO
            -- =========================
            COALESCE(ce.monto_efectivo, 0) AS monto_efectivo,

            -- =========================
            -- Datos del cobro
            -- =========================
            COALESCE(cc.numero_documento, 'N/A') AS numero_documento,
            COALESCE(cc.nro_voucher, 'N/A') AS nro_voucher,
            COALESCE(cc.portador, 'N/A') AS portador,

            -- =========================
            -- Forma de cobro
            -- =========================
            fc.id AS forma_cobro_id,
            fc.for_cob_descripcion AS forma_cobro,

            -- =========================
            -- Cliente
            -- =========================
            cli.id AS clientes_id,
            cli.cli_nombre,
            cli.cli_apellido,
            cli.cli_ruc,
            cli.cli_telefono,
            cli.cli_correo,
            cli.cli_direccion,

            -- =========================
            -- Venta asociada
            -- =========================
            cc.ventas_cab_id,
            'VENTA NRO: ' || TO_CHAR(cc.ventas_cab_id, '0000000') AS venta_nro,

            -- =========================
            -- Empresa / Sucursal
            -- =========================
            e.emp_razon_social,
            s.suc_razon_social,

            -- =========================
            -- Usuario
            -- =========================
            u.name AS encargado,

            -- =========================
            -- Caja (DESDE APERTURA)
            -- =========================
            ac.id AS apertura_cierre_caja_id,
            ac.estado AS aper_cier_estado,
            ca.caja_descripcion AS caja,

            -- =========================
            -- Entidades opcionales (resumen)
            -- =========================
            cc.entidad_emisora_id,
            COALESCE(ee.ent_emis_nombre, 'N/A') AS entidad_emisora,

            cc.marca_tarjeta_id,
            COALESCE(mt.marca_nombre, 'N/A') AS marca_tarjeta,

            cc.entidad_adherida_id,
            COALESCE(ea.ent_adh_nombre, 'N/A') AS entidad_adherida

        FROM cobros_cab cc

        JOIN forma_cobro fc ON fc.id = cc.forma_cobro_id
        JOIN clientes cli   ON cli.id = cc.clientes_id
        JOIN empresa e      ON e.id  = cc.empresa_id
        JOIN sucursal s     ON s.empresa_id  = cc.sucursal_id
        JOIN users u        ON u.id  = cc.user_id

        JOIN apertura_cierre_caja ac ON ac.id = cc.apertura_cierre_caja_id
        JOIN caja ca                ON ca.id = ac.caja_id

        LEFT JOIN entidad_emisora ee  ON ee.id = cc.entidad_emisora_id
        LEFT JOIN marca_tarjeta mt    ON mt.id = cc.marca_tarjeta_id
        LEFT JOIN entidad_adherida ea ON ea.id = cc.entidad_adherida_id

        -- 👇 EFECTIVO
        LEFT JOIN cobro_efectivo ce ON ce.cobros_cab_id = cc.id

        ORDER BY cc.id DESC
    ");
}
public function store(Request $r)
{
    $r->validate([
        'cobro_fecha' => 'required|date',
        'forma_cobro_id' => 'required|integer|exists:forma_cobro,id',
        'clientes_id' => 'required|integer|exists:clientes,id',
        'user_id' => 'required|integer|exists:users,id',
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
        $totalMedios =
            (float)($r->monto_efectivo ?? 0) +
            (float)($r->monto_tarjeta ?? 0) +
            (float)($r->monto_cheque ?? 0);

        if (abs($totalMedios - (float)$r->cobro_importe) > 0.01) {
            throw new \Exception('La suma de los medios de cobro no coincide con el importe');
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
            'nro_voucher' => $r->nro_voucher,
            'portador' => $r->portador,
            'fecha_cobro_diferido' => $r->fecha_cobro_diferido,

            'entidad_emisora_id' => $r->entidad_emisora_id,
            'marca_tarjeta_id' => $r->marca_tarjeta_id,
            'entidad_adherida_id' => $r->entidad_adherida_id,

            'forma_cobro_id' => $r->forma_cobro_id,
            'clientes_id' => $r->clientes_id,
            'ventas_cab_id' => $ventaId,
            'user_id' => $r->user_id,

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
        // 7) Detalle TARJETA
        // ==================================================
        if ($r->filled('monto_tarjeta') && (float)$r->monto_tarjeta > 0) {
            DB::table('cobros_tarjeta')->insert([
                'cobros_cab_id' => $cobroId,
                'entidad_emisora_tarjeta_id' => $r->entidad_emisora_tarjeta_id,
                'marca_tarjeta_tarjeta_id' => $r->marca_tarjeta_tarjeta_id,
                'entidad_adherida_tarjeta_id' => $r->entidad_adherida_tarjeta_id,
                'nro_tarjeta' => $r->nro_tarjeta,
                'fecha_vencimiento' => $r->fecha_venc_tarjeta,
                'nro_voucher' => $r->nro_voucher_tarjeta,
                'monto_tarjeta' => $r->monto_tarjeta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==================================================
        // 8) Detalle CHEQUE
        // ==================================================
        if ($r->filled('monto_cheque') && (float)$r->monto_cheque > 0) {
            DB::table('cobros_cheque')->insert([
                'cobros_cab_id'             => $cobroId,
                'entidad_emisora_cheque_id' => $r->entidad_emisora_cheque_id,
                'nro_cheque'                => $r->nro_cheque,
                'fecha_vencimiento'         => $r->fecha_venc_cheque,
                'monto_cheque'              => $r->monto_cheque,
                'estado_cheque'             => 'RECIBIDO',
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        }

        // ==================================================
        // 9) Detalle EFECTIVO
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
    ]);

    DB::beginTransaction();

    try {

        // ==================================================
        // 🔹 Normalizar campos opcionales
        // ==================================================
        foreach ([
            'numero_documento',
            'nro_voucher',
            'portador',
            'fecha_cobro_diferido',
            'cobro_observacion'
        ] as $campo) {
            if (
                !$r->has($campo) ||
                $r->$campo === '' ||
                $r->$campo === 'N/A' ||
                strtolower((string)$r->$campo) === 'null'
            ) {
                $r->merge([$campo => null]);
            }
        }

        // ==================================================
        // 🔹 Actualizar cabecera
        // ==================================================
        $cobro->update([
            'cobro_fecha'          => $r->cobro_fecha,
            'forma_cobro_id'       => $r->forma_cobro_id,
            'cobro_observacion'    => $r->cobro_observacion,
            'numero_documento'     => $r->numero_documento,
            'nro_voucher'          => $r->nro_voucher,
            'portador'             => $r->portador,
            'fecha_cobro_diferido' => $r->fecha_cobro_diferido,
        ]);

        // ==================================================
        // 🔹 Obtener forma de cobro
        // ==================================================
        $forma = DB::table('forma_cobro')
            ->where('id', $r->forma_cobro_id)
            ->value('for_cob_descripcion');

        // ==================================================
        // 🔹 Validar suma de medios
        // ==================================================
        $totalMedios =
            (float)($r->monto_efectivo ?? 0) +
            (float)($r->monto_tarjeta ?? 0) +
            (float)($r->monto_cheque ?? 0);

        if (abs($totalMedios - (float)$cobro->cobro_importe) > 0.01) {
            throw new \Exception('La suma de los medios de cobro no coincide con el importe');
        }

        // ==================================================
        // 🔹 SINCRONIZAR CABECERA SEGÚN FORMA
        // ==================================================
        if ($forma === 'Tarjeta') {

            $cobro->update([
                'entidad_emisora_id'  => $r->entidad_emisora_tarjeta_id,
                'marca_tarjeta_id'    => $r->marca_tarjeta_tarjeta_id,
                'entidad_adherida_id' => $r->entidad_adherida_tarjeta_id,
            ]);

        } elseif ($forma === 'Cheque') {

            $cobro->update([
                'entidad_emisora_id'  => $r->entidad_emisora_cheque_id,
                'marca_tarjeta_id'    => null,
                'entidad_adherida_id' => null,
            ]);

        } else { // EFECTIVO

            $cobro->update([
                'entidad_emisora_id'  => null,
                'marca_tarjeta_id'    => null,
                'entidad_adherida_id' => null,
            ]);
        }

        // ==================================================
        // 🔹 TARJETA (DETALLE)
        // ==================================================
        if ($forma === 'Tarjeta' && (float)$r->monto_tarjeta > 0) {

            $dataTarjeta = [
                'entidad_emisora_tarjeta_id'  => $r->entidad_emisora_tarjeta_id,
                'marca_tarjeta_tarjeta_id'    => $r->marca_tarjeta_tarjeta_id,
                'entidad_adherida_tarjeta_id' => $r->entidad_adherida_tarjeta_id,
                'nro_tarjeta'                 => $r->nro_tarjeta,
                'fecha_vencimiento'           => $r->fecha_venc_tarjeta,
                'nro_voucher'                 => $r->nro_voucher_tarjeta,
                'monto_tarjeta'               => $r->monto_tarjeta,
                'updated_at'                  => now(),
            ];

            DB::table('cobros_tarjeta')->updateOrInsert(
                ['cobros_cab_id' => $id],
                array_merge($dataTarjeta, ['created_at' => now()])
            );

        } else {
            DB::table('cobros_tarjeta')
                ->where('cobros_cab_id', $id)
                ->delete();
        }

        // ==================================================
        // 🔹 CHEQUE (DETALLE)
        // ==================================================
        if ($forma === 'Cheque' && (float)$r->monto_cheque > 0) {

            $dataCheque = [
                'entidad_emisora_cheque_id' => $r->entidad_emisora_cheque_id,
                'nro_cheque'                => $r->nro_cheque,
                'fecha_vencimiento'         => $r->fecha_venc_cheque,
                'monto_cheque'              => $r->monto_cheque,
                'updated_at'                => now(),
            ];

            DB::table('cobros_cheque')->updateOrInsert(
                ['cobros_cab_id' => $id],
                array_merge($dataCheque, [
                    'estado_cheque' => 'RECIBIDO',
                    'created_at'    => now(),
                ])
            );

        } else {
            DB::table('cobros_cheque')
                ->where('cobros_cab_id', $id)
                ->delete();
        }

        // ==================================================
        // 🔹 EFECTIVO (DETALLE)
        // ==================================================
        if ($forma === 'Efectivo' && (float)$r->monto_efectivo > 0) {

            DB::table('cobro_efectivo')->updateOrInsert(
                ['cobros_cab_id' => $id],
                [
                    'monto_efectivo' => $r->monto_efectivo,
                    'updated_at'     => now(),
                    'created_at'     => now(),
                ]
            );

        } else {
            DB::table('cobro_efectivo')
                ->where('cobros_cab_id', $id)
                ->delete();
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
