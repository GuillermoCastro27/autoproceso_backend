<?php

namespace App\Http\Controllers;

use App\Mail\ReclamoEstadoMail;
use App\Models\ReclamoCliCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ReclamoCliCabController extends Controller
{
    private function portalUrl()
    {
        return rtrim(env('PORTAL_SEGUIMIENTO_URL', 'http://localhost/taller_front/seguimiento_reclamo/index.html'), '/');
    }
    private function datosEmail(ReclamoCliCab $reclamo, array $cliente): array
    {
        $empresa  = DB::table('empresa')->where('id', $reclamo->empresa_id)->value('emp_razon_social');
        $sucursal = DB::table('sucursal')->where('id', $reclamo->sucursal_id)->value('suc_razon_social');
        $venta    = $reclamo->venta_cab_id
                        ? DB::table('ventas_cab')->where('id', $reclamo->venta_cab_id)->first()
                        : null;

        return [
            'id'          => $reclamo->id,
            'estado'      => $reclamo->rec_cli_cab_estado,
            'prioridad'   => $reclamo->rec_cli_cab_prioridad,
            'fecha'       => date('d/m/Y H:i', strtotime($reclamo->rec_cli_cab_fecha)),
            'fecha_inicio'=> $reclamo->rec_cli_cab_fecha_inicio
                                ? date('d/m/Y H:i', strtotime($reclamo->rec_cli_cab_fecha_inicio))
                                : null,
            'fecha_fin'   => $reclamo->rec_cli_cab_fecha_fin
                                ? date('d/m/Y H:i', strtotime($reclamo->rec_cli_cab_fecha_fin))
                                : null,
            'observacion' => $reclamo->rec_cli_cab_observacion,
            'cli_nombre'  => $cliente['cli_nombre'],
            'cli_apellido'=> $cliente['cli_apellido'],
            'empresa'     => $empresa  ?? '—',
            'sucursal'    => $sucursal ?? '—',
            'nro_venta'   => $venta ? str_pad($venta->id, 7, '0', STR_PAD_LEFT) : null,
            'venta_fecha' => $venta && $venta->vent_fecha ? date('d/m/Y', strtotime($venta->vent_fecha)) : null,
        ];
    }

    // -------------------------------------------------------
    // Envía el email al cliente si tiene correo registrado
    // -------------------------------------------------------
    private function enviarEmail(ReclamoCliCab $reclamo): void
    {
        $cliente = DB::table('clientes')->where('id', $reclamo->clientes_id)->first();

        if (!$cliente || empty($cliente->cli_correo)) {
            return;
        }

        $datos = $this->datosEmail($reclamo, (array) $cliente);

        Mail::to($cliente->cli_correo)->send(new ReclamoEstadoMail($datos));
    }

    // -------------------------------------------------------
    // LISTAR (admin)
    // -------------------------------------------------------
    public function read()
    {
        return DB::select("SELECT
            rcc.id,
            TO_CHAR(rcc.rec_cli_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha,
            TO_CHAR(rcc.rec_cli_cab_fecha_inicio, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha_inicio,
            TO_CHAR(rcc.rec_cli_cab_fecha_fin, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha_fin,
            rcc.rec_cli_cab_observacion,
            rcc.rec_cli_cab_prioridad,
            rcc.rec_cli_cab_estado,
            rcc.token_seguimiento,

            rcc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,

            rcc.empresa_id,
            e.emp_razon_social AS emp_razon_social,

            rcc.clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            rcc.funcionario_id,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,

            rcc.venta_cab_id,
            TO_CHAR(v.vent_fecha, 'dd/mm/yyyy') AS venta_fecha,

            rcc.created_at,
            rcc.updated_at

        FROM reclamo_cli_cab rcc
        JOIN sucursal s  ON s.id = rcc.sucursal_id
        JOIN empresa e   ON e.id = rcc.empresa_id
        JOIN clientes c  ON c.id = rcc.clientes_id
        JOIN funcionario f ON f.id = rcc.funcionario_id
        LEFT JOIN ventas_cab v ON v.id = rcc.venta_cab_id
        ORDER BY rcc.id DESC");
    }

    // -------------------------------------------------------
    // CREAR
    // -------------------------------------------------------
    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'  => 'required',
            'rec_cli_cab_fecha'        => 'required',
            'rec_cli_cab_fecha_inicio' => 'required',
            'rec_cli_cab_fecha_fin'    => 'required',
            'rec_cli_cab_prioridad'    => 'required',
            'rec_cli_cab_estado'       => 'required',
            'clientes_id'              => 'required',
            'funcionario_id'           => 'nullable',
            'empresa_id'               => 'required',
            'sucursal_id'              => 'required',
            'venta_cab_id'             => 'nullable|exists:ventas_cab,id',
        ]);

        $datosValidados['funcionario_id']    = auth()->user()->funcionario_id;
        $datosValidados['token_seguimiento'] = Str::random(48);

        $reclamo = ReclamoCliCab::create($datosValidados);

        $this->enviarEmail($reclamo);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $reclamo,
        ], 200);
    }

    // -------------------------------------------------------
    // EDITAR
    // -------------------------------------------------------
    public function update(Request $r, $id)
    {
        $reclamo = ReclamoCliCab::find($id);

        if (!$reclamo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'  => 'required',
            'rec_cli_cab_fecha'        => 'required',
            'rec_cli_cab_fecha_inicio' => 'required',
            'rec_cli_cab_fecha_fin'    => 'required',
            'rec_cli_cab_prioridad'    => 'required',
            'rec_cli_cab_estado'       => 'required',
            'clientes_id'              => 'required',
            'empresa_id'               => 'required',
            'sucursal_id'              => 'required',
            'venta_cab_id'             => 'nullable|exists:ventas_cab,id',
        ]);

        $reclamo->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $reclamo,
        ], 200);
    }

    // -------------------------------------------------------
    // ANULAR
    // -------------------------------------------------------
    public function anular($id)
    {
        $reclamo = ReclamoCliCab::find($id);

        if (!$reclamo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($reclamo->rec_cli_cab_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El reclamo ya se encuentra anulado', 'tipo' => 'warning'], 200);
        }

        $reclamo->update(['rec_cli_cab_estado' => 'ANULADO']);

        $this->enviarEmail($reclamo);

        return response()->json([
            'mensaje'  => 'Reclamo ANULADO con éxito',
            'tipo'     => 'success',
            'registro' => $reclamo,
        ], 200);
    }

    // -------------------------------------------------------
    // PROCESAR (PENDIENTE → EN PROCESO)
    // -------------------------------------------------------
    public function procesar($id)
    {
        $reclamo = ReclamoCliCab::find($id);

        if (!$reclamo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($reclamo->rec_cli_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden procesar reclamos en estado PENDIENTE',
                'tipo'    => 'warning',
            ], 200);
        }

        $reclamo->update(['rec_cli_cab_estado' => 'EN PROCESO']);

        $this->enviarEmail($reclamo);

        return response()->json([
            'mensaje'  => 'Reclamo pasado a EN PROCESO',
            'tipo'     => 'success',
            'registro' => $reclamo,
        ], 200);
    }

    // -------------------------------------------------------
    // RESOLVER (EN PROCESO → RESUELTO)
    // -------------------------------------------------------
    public function resolver($id)
    {
        $reclamo = ReclamoCliCab::find($id);

        if (!$reclamo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($reclamo->rec_cli_cab_estado !== 'EN PROCESO') {
            return response()->json([
                'mensaje' => 'Solo se pueden resolver reclamos EN PROCESO',
                'tipo'    => 'warning',
            ], 200);
        }

        $reclamo->update(['rec_cli_cab_estado' => 'RESUELTO']);

        $this->enviarEmail($reclamo);

        return response()->json([
            'mensaje'  => 'Reclamo RESUELTO con éxito',
            'tipo'     => 'success',
            'registro' => $reclamo,
        ], 200);
    }

    // -------------------------------------------------------
    // SEGUIMIENTO PÚBLICO (sin auth) — consulta por token
    // -------------------------------------------------------
    public function seguimiento($token)
    {
        $reclamo = DB::selectOne("
            SELECT
                rcc.id,
                TO_CHAR(rcc.rec_cli_cab_fecha,        'DD/MM/YYYY HH24:MI') AS fecha_registro,
                TO_CHAR(rcc.rec_cli_cab_fecha_inicio, 'DD/MM/YYYY HH24:MI') AS fecha_inicio,
                TO_CHAR(rcc.rec_cli_cab_fecha_fin,    'DD/MM/YYYY HH24:MI') AS fecha_fin,
                rcc.rec_cli_cab_estado     AS estado,
                rcc.rec_cli_cab_prioridad  AS prioridad,
                rcc.rec_cli_cab_observacion AS observacion,

                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,

                e.emp_razon_social,
                s.suc_razon_social

            FROM reclamo_cli_cab rcc
            JOIN clientes c  ON c.id = rcc.clientes_id
            JOIN empresa e   ON e.id = rcc.empresa_id
            JOIN sucursal s  ON s.id = rcc.sucursal_id

            WHERE rcc.token_seguimiento = ?
        ", [$token]);

        if (!$reclamo) {
            return response()->json(['mensaje' => 'Reclamo no encontrado', 'tipo' => 'error'], 404);
        }

        return response()->json($reclamo);
    }

    // -------------------------------------------------------
    // INFORME
    // -------------------------------------------------------
    public function buscarInforme(Request $r)
    {
        return DB::select("
            SELECT
                rcc.id,
                TO_CHAR(rcc.rec_cli_cab_fecha, 'dd/mm/yyyy') AS fecha,
                rcc.rec_cli_cab_estado AS estado,
                rcc.rec_cli_cab_prioridad AS prioridad,
                rcc.rec_cli_cab_observacion AS observacion,
                c.cli_nombre || ' ' || c.cli_apellido AS cliente,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social AS sucursal,
                e.emp_razon_social AS empresa
            FROM reclamo_cli_cab rcc
            JOIN clientes c ON c.id = rcc.clientes_id
            JOIN funcionario f ON f.id = rcc.funcionario_id
            JOIN sucursal s ON s.id = rcc.sucursal_id
            JOIN empresa e ON e.id = rcc.empresa_id
            WHERE rcc.rec_cli_cab_fecha BETWEEN ? AND ?
            ORDER BY rcc.rec_cli_cab_fecha ASC
        ", [$r->desde, $r->hasta]);
    }
}
