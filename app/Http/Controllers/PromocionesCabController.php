<?php

namespace App\Http\Controllers;

use App\Models\PromocionesCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PromocionesCabController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT 
                pc.id,
                pc.prom_cab_nombre,
                pc.prom_cab_observaciones,
                TO_CHAR(pc.prom_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_registro,
                TO_CHAR(pc.prom_cab_fecha_inicio, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_inicio,
                TO_CHAR(pc.prom_cab_fecha_fin, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_fin,
                pc.prom_cab_estado,
                pc.sucursal_id,
                s.suc_razon_social,
                pc.empresa_id,
                e.emp_razon_social,
                pc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                pc.tipo_promociones_id,
                tp.tipo_prom_nombre AS tipo_prom_nombre,
                tp.tipo_prom_modo AS tipo_prom_modo,
                tp.tipo_prom_valor AS tipo_prom_valor,
                pc.created_at,
                pc.updated_at
            FROM promociones_cab pc
            JOIN sucursal s ON s.id = pc.sucursal_id
            JOIN empresa e  ON e.id = pc.empresa_id
            JOIN funcionario f ON f.id = pc.funcionario_id
            JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
            ORDER BY pc.id DESC;
        ");
    }

    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'prom_cab_observaciones' => 'required',
            'prom_cab_nombre' => 'required',
            'prom_cab_fecha_registro' => 'required',
            'prom_cab_fecha_inicio' => 'required',
            'prom_cab_fecha_fin' => 'required',
            'prom_cab_estado' => 'required',
            'tipo_promociones_id' => 'required',
            'funcionario_id' => 'nullable',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);

        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
        $promocioncab = PromocionesCab::create($datosValidados);
        $promocioncab->save();

        return response()->json([
            'mensaje' => 'Registro creado con exito',
            'tipo' => 'success',
            'registro' => $promocioncab
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'prom_cab_observaciones' => 'required',
            'prom_cab_nombre' => 'required',
            'prom_cab_fecha_registro' => 'required',
            'prom_cab_fecha_inicio' => 'required',
            'prom_cab_fecha_fin' => 'required',
            'prom_cab_estado' => 'required',
            'tipo_promociones_id' => 'required',
            'funcionario_id' => 'nullable',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);

        $promocioncab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con exito',
            'tipo' => 'success',
            'registro' => $promocioncab
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'prom_cab_observaciones' => 'required',
            'prom_cab_nombre' => 'required',
            'prom_cab_fecha_registro' => 'required',
            'prom_cab_fecha_inicio' => 'required',
            'prom_cab_fecha_fin' => 'required',
            'prom_cab_estado' => 'required',
            'tipo_promociones_id' => 'required',
            'funcionario_id' => 'nullable',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);

        $promocioncab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro anulado con exito',
            'tipo' => 'success',
            'registro' => $promocioncab
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $promocioncab = PromocionesCab::find($id);
        if (!$promocioncab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'prom_cab_observaciones' => 'required',
            'prom_cab_nombre' => 'required',
            'prom_cab_fecha_registro' => 'required',
            'prom_cab_fecha_inicio' => 'required',
            'prom_cab_fecha_fin' => 'required',
            'prom_cab_estado' => 'required',
            'tipo_promociones_id' => 'required',
            'funcionario_id' => 'nullable',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);

        $promocioncab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro confirmado con exito',
            'tipo' => 'success',
            'registro' => $promocioncab
        ], 200);
    }

   public function buscar(Request $r)
{
    $texto  = $r->input('texto');
    $funcId = $r->input('funcionario_id');

    // 🔹 Primero: anula automáticamente las vencidas
    DB::update("
        UPDATE promociones_cab
        SET prom_cab_estado = 'ANULADO'
        WHERE prom_cab_estado = 'CONFIRMADO'
        AND prom_cab_fecha_fin < CURRENT_TIMESTAMP
    ");

    // 🔹 Luego: busca solo las vigentes
    return DB::select("
        SELECT 
            pc.id AS promociones_cab_id,
            pc.prom_cab_nombre,
            pc.prom_cab_observaciones,
            TO_CHAR(pc.prom_cab_fecha_registro, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_registro,
            TO_CHAR(pc.prom_cab_fecha_inicio, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_inicio,
            TO_CHAR(pc.prom_cab_fecha_fin, 'DD/MM/YYYY HH24:MI:SS') AS prom_cab_fecha_fin,
            pc.prom_cab_estado,
            pc.tipo_promociones_id,
            tp.tipo_prom_nombre AS tipo_prom_nombre,
            f.id AS funcionario_id,
            f.fun_nom || ' ' || f.fun_apellido AS encargado,
            pc.empresa_id,
            e.emp_razon_social,
            pc.sucursal_id,
            s.suc_razon_social,
            'PROMOCIÓN NRO: ' || TO_CHAR(pc.id, '0000000') || 
            ' (' || pc.prom_cab_nombre || ')' AS prom_cab_nombre
        FROM promociones_cab pc
        JOIN funcionario f ON f.id = pc.funcionario_id
        JOIN empresa e ON e.id = pc.empresa_id
        JOIN sucursal s ON s.id = pc.sucursal_id
        JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
        WHERE 
            pc.prom_cab_estado = 'CONFIRMADO'
            AND pc.funcionario_id = {$funcId}
            AND CURRENT_TIMESTAMP BETWEEN pc.prom_cab_fecha_inicio AND pc.prom_cab_fecha_fin
            AND (
                pc.prom_cab_nombre ILIKE '%{$texto}%'
                OR pc.prom_cab_observaciones ILIKE '%{$texto}%'
                OR tp.tipo_prom_nombre ILIKE '%{$texto}%'
                OR TO_CHAR(pc.id, '0000000') ILIKE '%{$texto}%'
            )
        ORDER BY pc.id DESC
    ");
}

}
