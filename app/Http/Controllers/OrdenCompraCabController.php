<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraCab;
use App\Models\Presupuesto;
use App\Models\Pedido;
use App\Models\OrdenCompraDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                o.id,
                COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
                o.ord_comp_fecha,
                o.ord_comp_estado,
                COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota,
                o.condicion_pago,
                COALESCE(pr.proveedor_id, o.proveedor_id) AS proveedor_id,
                p.prov_razonsocial,
                p.prov_ruc,
                p.prov_telefono,
                p.prov_correo,
                o.sucursal_id,
                s.suc_razon_social,
                o.empresa_id,
                e.emp_razon_social,
                o.presupuesto_id,
                o.pedido_id,
                CASE
                    WHEN pr.id IS NOT NULL THEN
                        'PRESUPUESTO NRO: ' || to_char(pr.id, '0000000') || ' VENCE EL: ' ||
                        COALESCE(to_char(pr.pre_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') || ' (' || pr.pre_observaciones || ')'
                    WHEN ped.id IS NOT NULL THEN
                        'PEDIDO DIRECTO NRO: ' || to_char(ped.id, '0000000') || ' (' || ped.ped_pbservaciones || ')'
                    ELSE 'SIN ORIGEN'
                END AS presupuesto,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario
            FROM orden_compra_cab o
            JOIN funcionario f ON f.id = o.funcionario_id
            JOIN sucursal s    ON s.id = o.sucursal_id
            JOIN empresa e     ON e.id = o.empresa_id
            LEFT JOIN presupuestos pr ON pr.id = o.presupuesto_id
            LEFT JOIN pedidos ped     ON ped.id = o.pedido_id
            LEFT JOIN proveedores p   ON p.id = COALESCE(pr.proveedor_id, o.proveedor_id)
        ");
    }

    public function store(Request $r)
    {
        if ($r->ord_comp_intervalo_fecha_vence === '') {
            $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
        }
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['ord_comp_cant_cuota' => null]);
        }

        if (empty($r->presupuesto_id) && empty($r->pedido_id)) {
            return response()->json(['mensaje' => 'Debe seleccionar un Presupuesto o un Pedido.', 'tipo' => 'error'], 422);
        }

        $datosValidados = $r->validate([
            'ord_comp_intervalo_fecha_vence' => 'required_if:condicion_pago,CREDITO|nullable|date',
            'ord_comp_fecha'                 => 'required|date',
            'ord_comp_estado'                => 'required',
            'ord_comp_cant_cuota'            => 'required_if:condicion_pago,CREDITO|nullable|integer|min:1',
            'funcionario_id'                 => 'nullable',
            'presupuesto_id'                 => 'nullable|integer',
            'pedido_id'                      => 'nullable|integer',
            'proveedor_id'                   => 'required|integer',
            'empresa_id'                     => 'required|integer',
            'sucursal_id'                    => 'required|integer',
            'condicion_pago'                 => 'required|string|max:20',
        ]);

        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
            $datosValidados['ord_comp_cant_cuota']            = null;
        }

        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
        $ordencompracab = OrdenCompraCab::create($datosValidados);

        if (!empty($r->presupuesto_id)) {
            $presupuesto = Presupuesto::find($r->presupuesto_id);
            if (!$presupuesto) {
                return response()->json(['mensaje' => 'Presupuesto no encontrado', 'tipo' => 'error'], 404);
            }
            $presupuesto->pre_estado = 'PROCESADO';
            $presupuesto->save();

            $detalles = DB::select("
                SELECT pd.*, i.item_decripcion,
                    pd.det_costo     AS orden_compra_det_costo,
                    pd.det_cantidad  AS orden_compra_det_cantidad,
                    pd.marca_id, pd.modelo_id,
                    i.tipo_impuesto_id
                FROM presupuestos_detalles pd
                JOIN items i ON i.id = pd.item_id
                WHERE pd.presupuesto_id = {$presupuesto->id}
            ");

            foreach ($detalles as $ocd) {
                $det = new OrdenCompraDet();
                $det->orden_compra_cab_id       = $ordencompracab->id;
                $det->item_id                   = $ocd->item_id;
                $det->orden_compra_det_costo    = $ocd->orden_compra_det_costo;
                $det->orden_compra_det_cantidad = $ocd->orden_compra_det_cantidad;
                $det->tipo_impuesto_id          = $ocd->tipo_impuesto_id;
                $det->deposito_id               = $ocd->deposito_id;
                $det->marca_id                  = $ocd->marca_id  ?? null;
                $det->modelo_id                 = $ocd->modelo_id ?? null;
                $det->save();
            }
        } else {
            $pedido = Pedido::find($r->pedido_id);
            if (!$pedido) {
                return response()->json(['mensaje' => 'Pedido no encontrado', 'tipo' => 'error'], 404);
            }
            $pedido->ped_estado = 'PROCESADO';
            $pedido->save();

            $detalles = DB::select("
                SELECT pd.item_id, pd.det_cantidad AS orden_compra_det_cantidad,
                    pd.deposito_id, pd.marca_id, pd.modelo_id,
                    i.item_costo AS orden_compra_det_costo,
                    i.tipo_impuesto_id
                FROM pedidos_detalles pd
                JOIN items i ON i.id = pd.item_id
                WHERE pd.pedidos_id = {$pedido->id}
            ");

            foreach ($detalles as $ocd) {
                $det = new OrdenCompraDet();
                $det->orden_compra_cab_id       = $ordencompracab->id;
                $det->item_id                   = $ocd->item_id;
                $det->orden_compra_det_costo    = $ocd->orden_compra_det_costo;
                $det->orden_compra_det_cantidad = $ocd->orden_compra_det_cantidad;
                $det->tipo_impuesto_id          = $ocd->tipo_impuesto_id;
                $det->deposito_id               = $ocd->deposito_id;
                $det->marca_id                  = $ocd->marca_id  ?? null;
                $det->modelo_id                 = $ocd->modelo_id ?? null;
                $det->save();
            }
        }

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $ordencompracab,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $ordencompracab = OrdenCompraCab::find($id);
        if (!$ordencompracab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($r->ord_comp_intervalo_fecha_vence === '') {
            $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
        }
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['ord_comp_cant_cuota' => null]);
        }

        $datosValidados = $r->validate([
            'ord_comp_intervalo_fecha_vence' => 'required_if:condicion_pago,CREDITO|nullable|date',
            'ord_comp_fecha'                 => 'required|date',
            'ord_comp_estado'                => 'required',
            'ord_comp_cant_cuota'            => 'required_if:condicion_pago,CREDITO|nullable|integer|min:1',
            'presupuesto_id'                 => 'nullable|integer',
            'pedido_id'                      => 'nullable|integer',
            'proveedor_id'                   => 'required|integer',
            'empresa_id'                     => 'required|integer',
            'sucursal_id'                    => 'required|integer',
            'condicion_pago'                 => 'required|string|max:20',
        ]);

        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
            $datosValidados['ord_comp_cant_cuota']            = null;
        }

        $ordencompracab->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $ordencompracab,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $ordencompracab = OrdenCompraCab::find($id);
        if (!$ordencompracab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($r->ord_comp_intervalo_fecha_vence === '') {
            $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
        }
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['ord_comp_cant_cuota' => null]);
        }

        $datosValidados = $r->validate([
            'ord_comp_intervalo_fecha_vence' => 'required_if:condicion_pago,CREDITO|nullable|date',
            'ord_comp_fecha'                 => 'required|date',
            'ord_comp_estado'                => 'required',
            'ord_comp_cant_cuota'            => 'required_if:condicion_pago,CREDITO|nullable|integer|min:1',
            'presupuesto_id'                 => 'nullable|integer',
            'pedido_id'                      => 'nullable|integer',
            'proveedor_id'                   => 'required|integer',
            'empresa_id'                     => 'required|integer',
            'sucursal_id'                    => 'required|integer',
            'condicion_pago'                 => 'required|string|max:20',
        ]);

        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
            $datosValidados['ord_comp_cant_cuota']            = null;
        }

        $datosValidados['ord_comp_estado'] = 'ANULADO';
        $ordencompracab->update($datosValidados);

        if ($ordencompracab->presupuesto_id) {
            $presupuesto = Presupuesto::find($ordencompracab->presupuesto_id);
            if ($presupuesto) {
                $presupuesto->pre_estado = 'CONFIRMADO';
                $presupuesto->save();
            }
        }

        if ($ordencompracab->pedido_id) {
            $pedido = Pedido::find($ordencompracab->pedido_id);
            if ($pedido) {
                $pedido->ped_estado = 'CONFIRMADO';
                $pedido->save();
            }
        }

        return response()->json([
            'mensaje'  => 'Orden de compra anulada con éxito',
            'tipo'     => 'success',
            'registro' => $ordencompracab,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $ordencompracab = OrdenCompraCab::find($id);
        if (!$ordencompracab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($r->ord_comp_intervalo_fecha_vence === '') {
            $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
        }
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['ord_comp_cant_cuota' => null]);
        }

        $datosValidados = $r->validate([
            'ord_comp_intervalo_fecha_vence' => 'required_if:condicion_pago,CREDITO|nullable|date',
            'ord_comp_fecha'                 => 'required|date',
            'ord_comp_estado'                => 'required',
            'ord_comp_cant_cuota'            => 'required_if:condicion_pago,CREDITO|nullable|integer|min:1',
            'presupuesto_id'                 => 'nullable|integer',
            'pedido_id'                      => 'nullable|integer',
            'proveedor_id'                   => 'required|integer',
            'empresa_id'                     => 'required|integer',
            'sucursal_id'                    => 'required|integer',
            'condicion_pago'                 => 'required|string|max:20',
        ]);

        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
            $datosValidados['ord_comp_cant_cuota']            = null;
        }

        $ordencompracab->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Orden confirmada con éxito',
            'tipo'     => 'success',
            'registro' => $ordencompracab,
        ], 200);
    }

    public function buscar(Request $r)
    {
        $funcId   = $r->input('funcionario_id');
        $funcName = $r->input('name');

        return DB::select("
            SELECT
                o.id AS orden_compra_cab_id,
                TO_CHAR(o.ord_comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS ord_comp_fecha,
                COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
                o.ord_comp_estado,
                o.condicion_pago,
                COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota,
                o.sucursal_id,
                s.suc_razon_social,
                o.empresa_id,
                e.emp_razon_social,
                o.funcionario_id,
                o.created_at,
                o.updated_at,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                o.proveedor_id,
                prov.prov_razonsocial,
                prov.prov_ruc,
                prov.prov_telefono,
                prov.prov_correo,
                'ORDEN COMPRA NRO: ' || TO_CHAR(o.id, '0000000') || ' VENCE EL: ' || TO_CHAR(o.ord_comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS ordencompra,
                COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS comp_intervalo_fecha_vence,
                COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS comp_cantidad_cuota
            FROM orden_compra_cab o
            JOIN funcionario f ON f.id = o.funcionario_id
            JOIN sucursal s    ON s.id = o.sucursal_id
            JOIN empresa e     ON e.id = o.empresa_id
            JOIN proveedores prov ON prov.id = o.proveedor_id
            WHERE o.ord_comp_estado = 'CONFIRMADO'
              AND o.funcionario_id = ?
              AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
        ", [$funcId, '%' . $funcName . '%']);
    }

    private function datosTicket($id)
    {
        $cab = DB::selectOne("
            SELECT
                o.id,
                o.ord_comp_fecha,
                COALESCE(TO_CHAR(o.ord_comp_intervalo_fecha_vence, 'DD/MM/YYYY'), 'N/A') AS ord_comp_intervalo_fecha_vence,
                o.ord_comp_estado,
                o.condicion_pago,
                COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota,
                e.emp_razon_social,
                COALESCE(e.emp_direccion, '') AS emp_direccion,
                COALESCE(e.emp_telefono, '') AS emp_telefono,
                s.suc_razon_social,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                COALESCE(prov.prov_razonsocial, '') AS prov_razonsocial,
                COALESCE(prov.prov_ruc, '') AS prov_ruc,
                COALESCE(prov.prov_telefono, '') AS prov_telefono,
                COALESCE(prov.prov_correo, '') AS prov_correo
            FROM orden_compra_cab o
            JOIN funcionario f ON f.id = o.funcionario_id
            JOIN sucursal s    ON s.id = o.sucursal_id
            JOIN empresa e     ON e.id = o.empresa_id
            LEFT JOIN presupuestos pr ON pr.id = o.presupuesto_id
            LEFT JOIN pedidos ped     ON ped.id = o.pedido_id
            LEFT JOIN proveedores prov ON prov.id = COALESCE(pr.proveedor_id, o.proveedor_id)
            WHERE o.id = ?
        ", [$id]);

        if (!$cab) return null;

        $detalles = DB::select("
            SELECT
                ocd.orden_compra_det_cantidad AS cantidad,
                ocd.orden_compra_det_costo AS costo,
                i.item_decripcion,
                ti.tip_imp_nom,
                COALESCE(d.dep_nombre, '-') AS dep_nombre,
                COALESCE(ma.marc_nom, '') AS marc_nom,
                COALESCE(mo.modelo_nom, '') AS modelo_nom
            FROM orden_compra_det ocd
            JOIN items i ON i.id = ocd.item_id
            JOIN tipo_impuesto ti ON ti.id = ocd.tipo_impuesto_id
            LEFT JOIN deposito d ON d.id = ocd.deposito_id
            LEFT JOIN marca ma ON ma.id = ocd.marca_id
            LEFT JOIN modelo mo ON mo.id = ocd.modelo_id
            WHERE ocd.orden_compra_cab_id = ?
        ", [$id]);

        return compact('cab', 'detalles');
    }

    public function imprimir($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Orden de compra no encontrada', 'tipo' => 'error'], 404);
        }
        return response()->json(['cab' => $data['cab'], 'detalles' => $data['detalles']]);
    }

    public function enviarTicket($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Orden de compra no encontrada', 'tipo' => 'error'], 404);
        }

        $cab = $data['cab'];
        if (empty($cab->prov_correo)) {
            return response()->json(['mensaje' => 'El proveedor no tiene correo registrado', 'tipo' => 'warning']);
        }

        $datos = array_merge((array) $cab, ['detalles' => $data['detalles']]);
        \Mail::to($cab->prov_correo)->send(new \App\Mail\TicketOrdenCompra($datos));

        return response()->json([
            'mensaje' => 'Orden enviada correctamente a ' . $cab->prov_correo,
            'tipo'    => 'success',
        ]);
    }

    public function buscarInforme(Request $r)
    {
        $desde = $r->query('desde');
        $hasta = $r->query('hasta');

        return DB::select("
            SELECT
                o.id,
                TO_CHAR(o.ord_comp_fecha, 'dd/mm/yyyy') AS fecha,
                COALESCE(TO_CHAR(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS entrega,
                o.ord_comp_estado AS estado,
                o.condicion_pago,
                COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS cuotas,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa,
                prov.prov_razonsocial AS proveedor,
                prov.prov_ruc AS ruc,
                CASE
                    WHEN pr.id IS NOT NULL THEN
                        'PRESUPUESTO NRO: ' || TO_CHAR(pr.id, '0000000') ||
                        ' VENCE EL: ' || COALESCE(TO_CHAR(pr.pre_vence, 'dd/mm/yyyy'), 'N/A') ||
                        ' (' || pr.pre_observaciones || ')'
                    WHEN ped.id IS NOT NULL THEN
                        'PEDIDO DIRECTO NRO: ' || TO_CHAR(ped.id, '0000000') || ' (' || ped.ped_pbservaciones || ')'
                    ELSE 'DIRECTO'
                END AS presupuesto
            FROM orden_compra_cab o
            JOIN funcionario f ON f.id = o.funcionario_id
            JOIN sucursal s    ON s.id = o.sucursal_id
            JOIN empresa e     ON e.id = o.empresa_id
            LEFT JOIN presupuestos pr ON pr.id = o.presupuesto_id
            LEFT JOIN pedidos ped     ON ped.id = o.pedido_id
            LEFT JOIN proveedores prov ON prov.id = COALESCE(pr.proveedor_id, o.proveedor_id)
            WHERE o.ord_comp_estado = 'PROCESADO'
              AND o.ord_comp_fecha BETWEEN ? AND ?
            ORDER BY o.ord_comp_fecha ASC
        ", [$desde, $hasta]);
    }
}
