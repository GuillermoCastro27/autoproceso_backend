<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    private function validarFechas(Request $r): ?array
    {
        $hoy = (new \DateTime())->format('d/m/Y');

        if ($r->ped_fecha) {
            $dt = \DateTime::createFromFormat('d/m/Y H:i:s', $r->ped_fecha);
            if (!$dt) return ['La fecha del pedido tiene un formato inválido.', 422];
            if ($dt->format('d/m/Y') !== $hoy)
                return ['La fecha del pedido debe ser la de hoy (' . $hoy . ').', 422];
        }

        if ($r->ped_vence) {
            $dtV = \DateTime::createFromFormat('d/m/Y H:i:s', $r->ped_vence);
            if (!$dtV) return ['El plazo de entrega tiene un formato inválido.', 422];
            if ($dtV < new \DateTime('today'))
                return ['El plazo de entrega no puede ser una fecha pasada.', 422];
            if (isset($dt) && $dtV < $dt)
                return ['El plazo de entrega debe ser igual o posterior a la fecha del pedido.', 422];
        }

        return null;
    }

    // 🔹 Centralizamos validaciones
    private function validarPedido(Request $r)
    {
        return $r->validate([
            'ped_vence'         => 'required',
            'ped_fecha'         => 'required',
            'ped_pbservaciones' => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'ped_estado'        => 'required|in:PENDIENTE,CONFIRMADO,ANULADO,PROCESADO',
            'funcionario_id'    => 'nullable',
            'empresa_id'        => 'required|integer|exists:empresa,id',
            'sucursal_id'       => 'required|integer|exists:sucursal,id',
        ], [
            'ped_pbservaciones.not_regex' => 'Las observaciones contienen caracteres no permitidos.',
            'ped_pbservaciones.max'       => 'Las observaciones no pueden superar 500 caracteres.',
            'ped_estado.in'               => 'El estado no es válido.',
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

    if ($err = $this->validarFechas($r))
        return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);

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

    if ($pedido->ped_estado !== 'PENDIENTE') {
        return response()->json(['mensaje' => 'Solo se puede modificar un pedido en estado PENDIENTE.', 'tipo' => 'warning'], 409);
    }

    if ($err = $this->validarFechas($r))
        return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);

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

        if ($pedido->ped_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El pedido ya está anulado.', 'tipo' => 'warning'], 409);
        }

        if ($pedido->ped_estado === 'PROCESADO') {
            return response()->json(['mensaje' => 'No se puede anular un pedido PROCESADO. Anule el presupuesto asociado primero.', 'tipo' => 'warning'], 409);
        }

        $pedido->ped_estado = 'ANULADO';
        $pedido->save();

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

        if ($pedido->ped_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede confirmar un pedido en estado PENDIENTE.', 'tipo' => 'warning'], 409);
        }

        $pedido->ped_estado = 'CONFIRMADO';
        $pedido->save();

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

    private function datosTicket($id)
    {
        $cab = DB::selectOne("
            SELECT
                p.id,
                TO_CHAR(p.ped_fecha, 'DD/MM/YYYY HH24:MI:SS') AS ped_fecha,
                TO_CHAR(p.ped_vence, 'DD/MM/YYYY HH24:MI:SS') AS ped_vence,
                p.ped_pbservaciones,
                p.ped_estado,
                e.emp_razon_social,
                COALESCE(e.emp_direccion, '') AS emp_direccion,
                COALESCE(e.emp_telefono, '') AS emp_telefono,
                s.suc_razon_social,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                f.fun_correo
            FROM pedidos p
            JOIN empresa e     ON e.id = p.empresa_id
            JOIN sucursal s    ON s.id = p.sucursal_id
            JOIN funcionario f ON f.id = p.funcionario_id
            WHERE p.id = ?
        ", [$id]);

        if (!$cab) return null;

        $detalles = DB::select("
            SELECT
                pd.det_cantidad AS cantidad,
                pd.cantidad_stock,
                i.item_decripcion,
                COALESCE(d.dep_nombre, '-') AS dep_nombre,
                COALESCE(ma.marc_nom, '') AS marc_nom,
                COALESCE(mo.modelo_nom, '') AS modelo_nom
            FROM pedidos_detalles pd
            JOIN items i ON i.id = pd.item_id
            LEFT JOIN deposito d  ON d.id  = pd.deposito_id
            LEFT JOIN marca ma    ON ma.id = pd.marca_id
            LEFT JOIN modelo mo   ON mo.id = pd.modelo_id
            WHERE pd.pedidos_id = ?
        ", [$id]);

        return compact('cab', 'detalles');
    }

    public function imprimir($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Pedido no encontrado', 'tipo' => 'error'], 404);
        }
        return response()->json(['cab' => $data['cab'], 'detalles' => $data['detalles']]);
    }

    public function enviarTicket($id)
    {
        $data = $this->datosTicket($id);
        if (!$data) {
            return response()->json(['mensaje' => 'Pedido no encontrado', 'tipo' => 'error'], 404);
        }

        $cab = $data['cab'];
        if (empty($cab->fun_correo)) {
            return response()->json(['mensaje' => 'El funcionario no tiene correo registrado', 'tipo' => 'warning']);
        }

        $datos = array_merge((array) $cab, ['detalles' => $data['detalles']]);
        \Mail::to($cab->fun_correo)->send(new \App\Mail\TicketPedido($datos));

        return response()->json([
            'mensaje' => 'Pedido enviado correctamente a ' . $cab->fun_correo,
            'tipo'    => 'success',
        ]);
    }
}