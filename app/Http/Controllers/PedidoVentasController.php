<?php

namespace App\Http\Controllers;

use App\Models\PedidoVentas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoVentasController extends Controller
{
    private function validarPedidoVenta(Request $r)
    {
        return $r->validate([
            'ped_ven_fecha'        => 'required',
            'ped_ven_vence'        => 'required',
            'ped_ven_observaciones'=> 'required',
            'ped_ven_estado'       => 'required',
            'funcionario_id'       => 'nullable',
            'empresa_id'           => 'required',
            'sucursal_id'          => 'required',
            'clientes_id'           => 'required'
        ]);
    }
    private function buscarPedidoVenta($id)
    {
        $pedido = PedidoVentas::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        return $pedido;
    }
    public function read()
    {
        return DB::select("
            SELECT 
                pv.id,
                TO_CHAR(pv.ped_ven_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ped_ven_fecha,
                TO_CHAR(pv.ped_ven_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_ven_vence,
                pv.ped_ven_observaciones,
                pv.ped_ven_estado,

                pv.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,

                pv.sucursal_id,
                s.suc_razon_social,

                pv.empresa_id,
                e.emp_razon_social,

                pv.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,

                pv.created_at,
                pv.updated_at
            FROM pedidos_ventas pv
            JOIN clientes c ON c.id = pv.clientes_id
            JOIN sucursal s ON s.id = pv.sucursal_id
            JOIN empresa  e ON e.id = pv.empresa_id
            JOIN funcionario f ON f.id = pv.funcionario_id
            ORDER BY pv.id DESC
        ");
    }
    public function store(Request $r)
    {
        $datos = $this->validarPedidoVenta($r);
        $datos['funcionario_id'] = auth()->user()->funcionario_id;
        $pedido = PedidoVentas::create($datos);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    public function update(Request $r, $id)
    {
        $pedido = $this->buscarPedidoVenta($id);
        if (!$pedido instanceof PedidoVentas) return $pedido;

        $pedido->update(
            $this->validarPedidoVenta($r)
        );

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    public function anular(Request $r, $id)
    {
        $pedido = $this->buscarPedidoVenta($id);
        if (!$pedido instanceof PedidoVentas) return $pedido;

        $pedido->update(
            $this->validarPedidoVenta($r)
        );

        return response()->json([
            'mensaje'  => 'Registro anulado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    public function confirmar(Request $r, $id)
    {
        $pedido = $this->buscarPedidoVenta($id);
        if (!$pedido instanceof PedidoVentas) return $pedido;

        $pedido->update(
            $this->validarPedidoVenta($r)
        );

        return response()->json([
            'mensaje'  => 'Registro confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    public function eliminar($id)
    {
        $pedido = $this->buscarPedidoVenta($id);
        if (!$pedido instanceof PedidoVentas) return $pedido;

        $pedido->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }
    public function buscar(Request $r)
    {
        $baseSql = "
            SELECT
                pv.id,
                TO_CHAR(pv.ped_ven_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_ven_vence,
                pv.ped_ven_observaciones,
                pv.ped_ven_estado,

                pv.id AS pedido_id,
                'PEDIDO VTA NRO: ' || TO_CHAR(pv.id, '0000000') ||
                ' (' || pv.ped_ven_observaciones || ')' AS pedido,

                pv.clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_telefono,
                c.cli_correo,
                c.cli_direccion,

                pv.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,

                pv.sucursal_id,
                s.suc_razon_social,

                pv.empresa_id,
                e.emp_razon_social

            FROM pedidos_ventas pv
            JOIN clientes c    ON c.id = pv.clientes_id
            JOIN funcionario f ON f.id = pv.funcionario_id
            JOIN sucursal s    ON s.id = pv.sucursal_id
            JOIN empresa  e    ON e.id = pv.empresa_id
            WHERE pv.ped_ven_estado = 'CONFIRMADO'
        ";

        // Búsqueda por cliente (desde gestionar_ventas — enfoque cliente primero)
        if ($r->clientes_id) {
            $texto = '%' . ($r->get('q', '')) . '%';
            return DB::select($baseSql . "
                AND pv.clientes_id = ?
                AND (
                    TO_CHAR(pv.id, '0000000') ILIKE ?
                    OR pv.ped_ven_observaciones ILIKE ?
                )
                ORDER BY pv.id DESC
                LIMIT 10
            ", [$r->clientes_id, $texto, $texto]);
        }

        // Fallback original: filtro por funcionario (compatibilidad otros módulos)
        return DB::select($baseSql . "
            AND pv.funcionario_id = ?
            AND (f.fun_nom || ' ' || f.fun_apellido) ILIKE ?
        ", [$r->funcionario_id, "%{$r->name}%"]);
    }

    public function buscarInforme(Request $r)
    {
        return DB::select("
            SELECT 
                pv.id,
                TO_CHAR(pv.ped_ven_fecha, 'dd/mm/yyyy') AS fecha,
                TO_CHAR(pv.ped_ven_vence, 'dd/mm/yyyy') AS entrega,
                pv.ped_ven_observaciones AS observaciones,
                pv.ped_ven_estado AS estado,

                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                c.cli_nombre || ' ' || c.cli_apellido AS cliente,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa
            FROM pedidos_ventas pv
            JOIN funcionario f ON f.id = pv.funcionario_id
            JOIN clientes c ON c.id = pv.clientes_id
            JOIN sucursal s ON s.id = pv.sucursal_id
            JOIN empresa  e ON e.id = pv.empresa_id
            WHERE pv.ped_ven_estado = 'PROCESADO'
              AND pv.ped_ven_fecha BETWEEN ? AND ?
            ORDER BY pv.ped_ven_fecha ASC
        ", [$r->desde, $r->hasta]);
    }
}
