<?php

namespace App\Http\Controllers;

use App\Models\InsumosCab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumosCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                ic.id,
                ic.orden_serv_cab_id,
                ic.ins_cab_fecha_registro,
                ic.ins_cab_estado,
                osc.empresa_id,
                e.emp_razon_social,
                osc.sucursal_id,
                s.suc_razon_social,
                osc.ord_serv_fecha,
                c.id   AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                et.equipo_nombre,
                m.marc_nom,
                mo.modelo_nom,
                COALESCE(rc.recep_cab_num_chasis, '') AS recep_cab_num_chasis
            FROM insumos_cab ic
            JOIN orden_serv_cab osc ON osc.id = ic.orden_serv_cab_id
            JOIN empresa e          ON e.id   = osc.empresa_id
            JOIN sucursal s         ON s.id   = osc.sucursal_id
            JOIN clientes c         ON c.id   = osc.clientes_id
            JOIN equipo_trabajo et  ON et.id  = osc.equipo_trabajo_id
            JOIN tipo_vehiculo tv   ON tv.id  = osc.tipo_vehiculo_id
            JOIN marca m            ON m.id   = tv.marca_id
            JOIN modelo mo          ON mo.id  = tv.modelo_id
            LEFT JOIN recep_cab rc  ON rc.id  = osc.recep_cab_id
            ORDER BY ic.id DESC
        ");
    }

    public function readById($id)
    {
        return DB::selectOne("
            SELECT
                ic.id,
                ic.orden_serv_cab_id,
                ic.ins_cab_fecha_registro,
                ic.ins_cab_estado,
                osc.empresa_id,
                e.emp_razon_social,
                osc.sucursal_id,
                s.suc_razon_social,
                osc.ord_serv_fecha,
                osc.ord_serv_observaciones,
                c.id   AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                et.id   AS equipo_trabajo_id,
                et.equipo_nombre,
                m.marc_nom,
                mo.modelo_nom,
                COALESCE(rc.recep_cab_num_chasis, '') AS recep_cab_num_chasis
            FROM insumos_cab ic
            JOIN orden_serv_cab osc ON osc.id = ic.orden_serv_cab_id
            JOIN empresa e          ON e.id   = osc.empresa_id
            JOIN sucursal s         ON s.id   = osc.sucursal_id
            JOIN clientes c         ON c.id   = osc.clientes_id
            JOIN equipo_trabajo et  ON et.id  = osc.equipo_trabajo_id
            JOIN tipo_vehiculo tv   ON tv.id  = osc.tipo_vehiculo_id
            JOIN marca m            ON m.id   = tv.marca_id
            JOIN modelo mo          ON mo.id  = tv.modelo_id
            LEFT JOIN recep_cab rc  ON rc.id  = osc.recep_cab_id
            WHERE ic.id = ?
        ", [$id]);
    }

    public function buscarOS(Request $r)
    {
        $q = '%' . ($r->q ?? '') . '%';
        return DB::select("
            SELECT
                osc.id,
                TO_CHAR(osc.id, 'FM0000000') AS nro_os,
                osc.ord_serv_fecha,
                osc.empresa_id,
                e.emp_razon_social,
                osc.sucursal_id,
                s.suc_razon_social,
                c.id   AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                et.id   AS equipo_trabajo_id,
                et.equipo_nombre,
                m.marc_nom,
                mo.modelo_nom,
                COALESCE(rc.recep_cab_num_chasis, '') AS recep_cab_num_chasis,
                osc.ord_serv_observaciones
            FROM orden_serv_cab osc
            JOIN empresa e          ON e.id   = osc.empresa_id
            JOIN sucursal s         ON s.id   = osc.sucursal_id
            JOIN clientes c         ON c.id   = osc.clientes_id
            JOIN equipo_trabajo et  ON et.id  = osc.equipo_trabajo_id
            JOIN tipo_vehiculo tv   ON tv.id  = osc.tipo_vehiculo_id
            JOIN marca m            ON m.id   = tv.marca_id
            JOIN modelo mo          ON mo.id  = tv.modelo_id
            LEFT JOIN recep_cab rc  ON rc.id  = osc.recep_cab_id
            WHERE osc.ord_serv_estado = 'CONFIRMADO'
              AND (
                    CAST(osc.id AS TEXT) ILIKE ?
                 OR c.cli_nombre  ILIKE ?
                 OR c.cli_apellido ILIKE ?
                 OR et.equipo_nombre ILIKE ?
              )
            ORDER BY osc.id DESC
            LIMIT 20
        ", [$q, $q, $q, $q]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'orden_serv_cab_id'    => 'required|integer|exists:orden_serv_cab,id',
            'ins_cab_fecha_registro' => 'required|date',
        ]);

        $cab = InsumosCab::create([
            'orden_serv_cab_id'      => $r->orden_serv_cab_id,
            'ins_cab_fecha_registro' => $r->ins_cab_fecha_registro,
            'ins_cab_estado'         => 'PENDIENTE',
        ]);

        return response()->json([
            'mensaje'  => 'Registro de insumos creado correctamente.',
            'tipo'     => 'success',
            'registro' => $cab,
        ], 201);
    }

    public function update(Request $r, $id)
    {
        $cab = InsumosCab::find($id);
        if (!$cab) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($cab->ins_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede modificar un registro en estado PENDIENTE.', 'tipo' => 'error'], 422);
        }

        $r->validate([
            'ins_cab_fecha_registro' => 'required|date',
        ]);

        $cab->update(['ins_cab_fecha_registro' => $r->ins_cab_fecha_registro]);

        return response()->json(['mensaje' => 'Registro actualizado correctamente.', 'tipo' => 'success', 'registro' => $cab]);
    }

    public function confirmar($id)
    {
        $cab = InsumosCab::find($id);
        if (!$cab) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($cab->ins_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede confirmar un registro en estado PENDIENTE.', 'tipo' => 'error'], 422);
        }

        $cab->update(['ins_cab_estado' => 'CONFIRMADO']);

        return response()->json(['mensaje' => 'Insumos confirmados correctamente.', 'tipo' => 'success']);
    }

    public function anular($id)
    {
        $cab = InsumosCab::find($id);
        if (!$cab) {
            return response()->json(['mensaje' => 'Registro no encontrado.', 'tipo' => 'error'], 404);
        }
        if ($cab->ins_cab_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El registro ya está anulado.', 'tipo' => 'error'], 422);
        }

        $cab->update(['ins_cab_estado' => 'ANULADO']);

        return response()->json(['mensaje' => 'Registro anulado correctamente.', 'tipo' => 'success']);
    }
}
