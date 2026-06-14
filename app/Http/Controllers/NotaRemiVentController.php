<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotaRemiVent;
use App\Models\NotaRemiVentDet;

class NotaRemiVentController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                nrv.*,
                TO_CHAR(nrv.nota_remi_vent_fecha, 'dd/mm/yyyy HH24:mi:ss') AS nota_remi_vent_fecha_formato,
                c.cli_nombre, c.cli_apellido, c.cli_ruc, c.cli_direccion, c.cli_telefono, c.cli_correo,
                s.suc_razon_social,
                e.emp_razon_social,
                'VENTA NRO: ' || TO_CHAR(nrv.ventas_cab_id, 'FM0000000') AS venta,
                TO_CHAR(nrv.ventas_cab_id, 'FM0000000') AS nro_venta,
                f.fun_nom  || ' ' || f.fun_apellido  AS funcionario,
                fe.fun_nom || ' ' || fe.fun_apellido AS funcionario_entrega_nombre,
                tvd.tv_det_placa,
                tv.tip_veh_nombre,
                COALESCE(m.marc_nom,  '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom,
                tv.tv_anio,
                tv.tv_color
            FROM nota_remi_vent nrv
            JOIN clientes c       ON c.id   = nrv.clientes_id
            JOIN sucursal s       ON s.id   = nrv.sucursal_id
            JOIN empresa e        ON e.id   = nrv.empresa_id
            LEFT JOIN ventas_cab v ON v.id  = nrv.ventas_cab_id
            JOIN funcionario f    ON f.id   = nrv.funcionario_id
            LEFT JOIN funcionario fe       ON fe.id  = nrv.funcionario_entrega_id
            LEFT JOIN tipo_vehiculo_det tvd ON tvd.id = nrv.tipo_vehiculo_det_id
            LEFT JOIN tipo_vehiculo tv      ON tv.id  = tvd.tipo_vehiculo_id
            LEFT JOIN marca m               ON m.id   = tv.marca_id
            LEFT JOIN modelo mo             ON mo.id  = tv.modelo_id
            ORDER BY nrv.id DESC
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'nota_remi_vent_fecha'           => 'required',
            'nota_remi_vent_observaciones'   => ['nullable', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'clientes_id'                    => 'required|integer|exists:clientes,id',
            'ventas_cab_id'                  => 'required|integer|exists:ventas_cab,id',
            'empresa_id'                     => 'required|integer|exists:empresa,id',
            'sucursal_id'                    => 'required|integer|exists:sucursal,id',
            'funcionario_entrega_id'         => 'nullable|integer|exists:funcionario,id',
            'tipo_vehiculo_det_id'           => 'nullable|integer|exists:tipo_vehiculo_det,id',
            'timbrado_id'                    => 'nullable|integer|exists:timbrado,id',
            'nota_remi_vent_nro_comprobante' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $nota = NotaRemiVent::create([
                'nota_remi_vent_fecha'           => $r->nota_remi_vent_fecha,
                'nota_remi_vent_observaciones'   => $r->nota_remi_vent_observaciones,
                'nota_remi_vent_estado'          => 'PENDIENTE',
                'clientes_id'                    => $r->clientes_id,
                'ventas_cab_id'                  => $r->ventas_cab_id,
                'funcionario_id'                 => auth()->user()->funcionario_id,
                'funcionario_entrega_id'         => $r->funcionario_entrega_id,
                'tipo_vehiculo_det_id'           => $r->tipo_vehiculo_det_id,
                'timbrado_id'                    => $r->timbrado_id,
                'nota_remi_vent_nro_comprobante' => $r->nota_remi_vent_nro_comprobante,
                'empresa_id'                     => $r->empresa_id,
                'sucursal_id'                    => $r->sucursal_id,
            ]);

            // 1. Ítems de la venta (pedidos normales)
            $itemsVenta = DB::select("
                SELECT item_id, vent_det_cantidad, vent_det_precio
                FROM ventas_det
                WHERE ventas_cab_id = ?
            ", [$r->ventas_cab_id]);

            foreach ($itemsVenta as $iv) {
                NotaRemiVentDet::create([
                    'nota_remi_vent_id'           => $nota->id,
                    'item_id'                     => $iv->item_id,
                    'nota_remi_vent_det_cantidad' => $iv->vent_det_cantidad,
                    'nota_remi_vent_det_precio'   => $iv->vent_det_precio,
                    'tipo_origen'                 => 'venta',
                    'orden_serv_cab_id'           => null,
                ]);
            }

            // 2. Insumos de órdenes de servicio vinculadas a la venta
            $ordenes = DB::select("
                SELECT orden_serv_cab_id
                FROM orden_serv_venta
                WHERE ventas_cab_id = ?
            ", [$r->ventas_cab_id]);

            foreach ($ordenes as $ord) {
                $insumos = DB::select("
                    SELECT item_id, ins_util_cantidad, ins_util_costo
                    FROM insumos_utilizados
                    WHERE orden_serv_cab_id = ?
                      AND ins_util_estado != 'ANULADO'
                ", [$ord->orden_serv_cab_id]);

                foreach ($insumos as $ins) {
                    NotaRemiVentDet::create([
                        'nota_remi_vent_id'           => $nota->id,
                        'item_id'                     => $ins->item_id,
                        'nota_remi_vent_det_cantidad' => $ins->ins_util_cantidad,
                        'nota_remi_vent_det_precio'   => $ins->ins_util_costo,
                        'tipo_origen'                 => 'servicio',
                        'orden_serv_cab_id'           => $ord->orden_serv_cab_id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'mensaje'  => 'Nota de remisión creada correctamente.',
                'tipo'     => 'success',
                'registro' => $nota,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al crear la nota de remisión: ' . $e->getMessage(),
                'tipo'    => 'error',
            ], 500);
        }
    }

    public function update(Request $r, $id)
    {
        $nota = NotaRemiVent::find($id);
        if (!$nota) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($nota->nota_remi_vent_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden modificar notas en estado PENDIENTE.', 'tipo' => 'warning'], 422);
        }

        $r->validate([
            'nota_remi_vent_fecha'         => 'required',
            'nota_remi_vent_observaciones' => 'nullable|string|max:200',
            'funcionario_entrega_id'       => 'nullable|integer|exists:funcionario,id',
            'tipo_vehiculo_det_id'         => 'nullable|integer|exists:tipo_vehiculo_det,id',
            'timbrado_id'                  => 'nullable|integer|exists:timbrado,id',
            'nota_remi_vent_nro_comprobante' => 'nullable|integer',
        ]);

        $nota->update([
            'nota_remi_vent_fecha'           => $r->nota_remi_vent_fecha,
            'nota_remi_vent_observaciones'   => $r->nota_remi_vent_observaciones,
            'funcionario_entrega_id'         => $r->funcionario_entrega_id,
            'tipo_vehiculo_det_id'           => $r->tipo_vehiculo_det_id,
            'timbrado_id'                    => $r->timbrado_id,
            'nota_remi_vent_nro_comprobante' => $r->nota_remi_vent_nro_comprobante,
        ]);

        return response()->json([
            'mensaje'  => 'Nota de remisión modificada con éxito.',
            'tipo'     => 'success',
            'registro' => $nota,
        ]);
    }

    public function imprimir($id)
    {
        $cab = DB::selectOne("
            SELECT
                nrv.*,
                TO_CHAR(nrv.nota_remi_vent_fecha, 'DD/MM/YYYY HH24:MI') AS nota_remi_vent_fecha_formato,
                e.emp_razon_social, e.emp_direccion, e.emp_telefono,
                s.suc_razon_social, s.suc_direccion, s.suc_telefono,
                c.cli_nombre, c.cli_apellido, c.cli_ruc, c.cli_direccion, c.cli_telefono,
                f.fun_nom  || ' ' || f.fun_apellido  AS funcionario,
                fe.fun_nom || ' ' || fe.fun_apellido AS funcionario_entrega_nombre,
                tvd.tv_det_placa,
                tv.tip_veh_nombre,
                tv.tv_anio, tv.tv_color,
                COALESCE(m.marc_nom,  '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom,
                t.tim_numero,
                TO_CHAR(t.tim_fecha_fin, 'DD/MM/YYYY') AS tim_fecha_fin,
                TO_CHAR(nrv.ventas_cab_id, 'FM0000000') AS nro_venta
            FROM nota_remi_vent nrv
            JOIN empresa e          ON e.id   = nrv.empresa_id
            JOIN sucursal s         ON s.id   = nrv.sucursal_id
            JOIN clientes c         ON c.id   = nrv.clientes_id
            JOIN funcionario f      ON f.id   = nrv.funcionario_id
            LEFT JOIN funcionario fe        ON fe.id  = nrv.funcionario_entrega_id
            LEFT JOIN tipo_vehiculo_det tvd ON tvd.id = nrv.tipo_vehiculo_det_id
            LEFT JOIN tipo_vehiculo tv      ON tv.id  = tvd.tipo_vehiculo_id
            LEFT JOIN marca m               ON m.id   = tv.marca_id
            LEFT JOIN modelo mo             ON mo.id  = tv.modelo_id
            LEFT JOIN timbrado t            ON t.id   = nrv.timbrado_id
            WHERE nrv.id = ?
        ", [$id]);

        if (!$cab) {
            return response()->json(['mensaje' => 'Nota de remisión no encontrada.', 'tipo' => 'error'], 404);
        }

        $detalles = DB::select("
            SELECT
                d.id,
                d.item_id,
                i.item_decripcion,
                d.nota_remi_vent_det_cantidad,
                d.nota_remi_vent_det_precio,
                d.tipo_origen,
                CASE
                    WHEN d.orden_serv_cab_id IS NOT NULL
                    THEN 'Servicio — ORDEN Nº ' || LPAD(d.orden_serv_cab_id::text, 7, '0')
                    ELSE 'Venta'
                END AS origen_label,
                ROUND((d.nota_remi_vent_det_cantidad * d.nota_remi_vent_det_precio)::numeric, 2) AS subtotal
            FROM nota_remi_vent_det d
            JOIN items i ON i.id = d.item_id
            WHERE d.nota_remi_vent_id = ?
            ORDER BY d.tipo_origen DESC, d.id ASC
        ", [$id]);

        return response()->json([
            'cab'      => $cab,
            'detalles' => $detalles,
        ]);
    }

    public function anular($id)
    {
        $nota = NotaRemiVent::find($id);
        if (!$nota) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($nota->nota_remi_vent_estado === 'ANULADA') {
            return response()->json(['mensaje' => 'La nota ya está anulada.', 'tipo' => 'warning'], 422);
        }

        $nota->nota_remi_vent_estado = 'ANULADA';
        $nota->save();

        return response()->json(['mensaje' => 'Nota de remisión anulada correctamente.', 'tipo' => 'success', 'registro' => $nota]);
    }

    public function confirmar($id)
    {
        $nota = NotaRemiVent::find($id);
        if (!$nota) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($nota->nota_remi_vent_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar notas en estado PENDIENTE.', 'tipo' => 'warning'], 422);
        }

        $nota->nota_remi_vent_estado = 'CONFIRMADA';
        $nota->save();

        return response()->json(['mensaje' => 'Nota de remisión confirmada correctamente.', 'tipo' => 'success', 'registro' => $nota]);
    }
}
