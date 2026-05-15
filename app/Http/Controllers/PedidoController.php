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
            'funcionario_id'   => 'nullable',
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
                p.funcionario_id,
                p.created_at,
                p.updated_at,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario
            FROM pedidos p
            JOIN sucursal s ON s.id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            JOIN funcionario f ON f.id = p.funcionario_id;
        ");
    }

   public function store(Request $r)
{
    DB::unprepared("SET myapp.usuario_id = '".(auth()->id() ?? 0)."'");
    DB::unprepared("SET myapp.usuario_nom = '".(auth()->user()->name ?? 'SIN USUARIO')."'");
    DB::unprepared("SET myapp.ip = '".request()->ip()."'");
    DB::unprepared("SET myapp.url = '".request()->fullUrl()."'");

    $datos = $this->validarPedido($r);
    $datos['funcionario_id'] = auth()->user()->funcionario_id;
    $pedido = Pedido::create($datos);

    return response()->json([
        'mensaje'  => 'Registro creado con éxito',
        'tipo'     => 'success',
        'registro' => $pedido
    ], 200);
}

    public function update(Request $r, $id)
{

    $pedido = $this->buscarPedido($id);
    if (!$pedido instanceof Pedido) return $pedido;

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

    public function buscar(Request $r)
    {
        $sql = "
            SELECT
                p.id,
                TO_CHAR(p.ped_vence, 'dd/mm/yyyy HH24:mi:ss') AS ped_vence,
                p.ped_pbservaciones,
                p.ped_estado,
                p.funcionario_id,
                p.created_at,
                p.updated_at,
                f.fun_nom || ' ' || f.fun_apellido AS encargado,
                p.id AS pedido_id,
                'PEDIDO NRO: ' || TO_CHAR(p.id, '0000000') || ' (' || p.ped_pbservaciones || ')' AS pedido,
                p.sucursal_id,
                s.suc_razon_social,
                p.empresa_id,
                e.emp_razon_social
            FROM pedidos p
            JOIN funcionario f ON f.id = p.funcionario_id
            JOIN sucursal s ON s.id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            WHERE p.ped_estado = 'CONFIRMADO'
              AND CAST(p.id AS TEXT) LIKE ?
        ";

        $params = ["%{$r->numero}%"];

        if ($r->filled('funcionario_id')) {
            $sql .= " AND p.funcionario_id = ?";
            $params[] = $r->funcionario_id;
        }

        return DB::select($sql, $params);
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
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa
            FROM pedidos p
            JOIN funcionario f ON f.id = p.funcionario_id
            JOIN sucursal s ON s.id = p.sucursal_id
            JOIN empresa e  ON e.id = p.empresa_id
            WHERE p.ped_estado = 'PROCESADO'
              AND p.ped_fecha BETWEEN ? AND ?
            ORDER BY p.ped_fecha ASC
        ", [$request->desde, $request->hasta]);
    }
}