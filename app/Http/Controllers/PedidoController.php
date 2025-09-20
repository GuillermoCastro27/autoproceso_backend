<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    // 🔹 Centralizamos validaciones
    private function validarPedido(Request $r)
    {
        return $r->validate([
            'ped_vence'        => 'required',
            'ped_fecha'        => 'required',
            'ped_pbservaciones'=> 'required',
            'ped_estado'       => 'required',
            'user_id'          => 'required',
            'empresa_id'       => 'required',
            'sucursal_id'      => 'required',
        ]);
    }

    // 🔹 Centralizamos búsqueda de pedido
    private function buscarPedido($id)
    {
        $pedido = Pedido::find($id);
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
                p.id,
                TO_CHAR(p.ped_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ped_fecha,
                TO_CHAR(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_vence,
                p.ped_pbservaciones,
                p.ped_estado,
                p.sucursal_id,
                s.suc_razon_social,
                p.empresa_id,
                e.emp_razon_social,
                p.user_id,
                p.created_at,
                p.updated_at,
                u.name,
                u.login
            FROM pedidos p
            JOIN sucursal s ON s.empresa_id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            JOIN users u    ON u.id = p.user_id;
        ");
    }

    public function store(Request $r)
    {
        $pedido = Pedido::create($this->validarPedido($r));

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $pedido = $this->buscarPedido($id);
        if (!$pedido instanceof Pedido) return $pedido; // Devuelve error si no existe

        $pedido->update($this->validarPedido($r));

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $pedido = $this->buscarPedido($id);
        if (!$pedido instanceof Pedido) return $pedido;

        $pedido->update($this->validarPedido($r));

        return response()->json([
            'mensaje'  => 'Registro anulado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $pedido = $this->buscarPedido($id);
        if (!$pedido instanceof Pedido) return $pedido;

        $pedido->update($this->validarPedido($r));

        return response()->json([
            'mensaje'  => 'Registro confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }

    public function eliminar($id)
    {
        $pedido = $this->buscarPedido($id);
        if (!$pedido instanceof Pedido) return $pedido;

        $pedido->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT 
                p.id,
                TO_CHAR(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_vence,
                p.ped_pbservaciones,
                p.ped_estado,
                p.user_id,
                p.created_at,
                p.updated_at,
                u.name,
                u.login,
                p.id AS pedido_id,
                'PEDIDO NRO: ' || TO_CHAR(p.id, '0000000') || ' (' || p.ped_pbservaciones || ')' AS pedido,
                p.sucursal_id,
                s.suc_razon_social,
                p.empresa_id,
                e.emp_razon_social
            FROM pedidos p
            JOIN users u   ON u.id = p.user_id
            JOIN sucursal s ON s.empresa_id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            WHERE p.ped_estado = 'CONFIRMADO'
              AND p.user_id = ?
              AND u.name ILIKE ?
        ", [$r->user_id, "%{$r->name}%"]);
    }

    public function buscarInforme(Request $request)
    {
        return DB::select("
            SELECT 
                p.id,
                TO_CHAR(p.ped_fecha, 'dd/mm/yyyy') AS fecha,
                TO_CHAR(p.ped_vence, 'dd/mm/yyyy') AS entrega,
                p.ped_pbservaciones AS observaciones,
                p.ped_estado AS estado,
                u.name AS encargado,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa
            FROM pedidos p
            JOIN users u   ON u.id = p.user_id
            JOIN sucursal s ON s.empresa_id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            WHERE p.ped_estado = 'PROCESADO'
              AND p.ped_fecha BETWEEN ? AND ?
            ORDER BY p.ped_fecha ASC
        ", [$request->desde, $request->hasta]);
    }
}