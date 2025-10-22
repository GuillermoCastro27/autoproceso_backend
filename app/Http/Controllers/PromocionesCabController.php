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
            pc.user_id,
            u.name,
            u.login,
            pc.tipo_promociones_id,
            tp.tipo_prom_nombre AS tipo_prom_nombre,
            pc.created_at,
            pc.updated_at
        FROM promociones_cab pc
        JOIN sucursal s ON s.empresa_id = pc.sucursal_id
        JOIN empresa e  ON e.id = pc.empresa_id
        JOIN users u    ON u.id = pc.user_id
        JOIN tipo_promociones tp ON tp.id = pc.tipo_promociones_id
        ORDER BY pc.id DESC;
    ");
}
public function store(Request $r){
        $datosValidados = $r->validate([
            'prom_cab_observaciones'=>'required',
            'prom_cab_nombre'=>'required',
            'prom_cab_fecha_registro'=>'required',
            'prom_cab_fecha_inicio'=>'required',
            'prom_cab_fecha_fin'=>'required',
            'prom_cab_estado'=>'required',
            'tipo_promociones_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $promocioncab = PromocionesCab::create($datosValidados);
        $promocioncab->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $promocioncab
        ],200);
    }
    public function update(Request $r, $id){
        $promocioncab = PromocionesCab::find($id);
        if(!$promocioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'prom_cab_observaciones'=>'required',
            'prom_cab_nombre'=>'required',
            'prom_cab_fecha_registro'=>'required',
            'prom_cab_fecha_inicio'=>'required',
            'prom_cab_fecha_fin'=>'required',
            'prom_cab_estado'=>'required',
            'tipo_promociones_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $promocioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $promocioncab
        ],200);
    }
    public function anular(Request $r, $id){
        $promocioncab = PromocionesCab::find($id);
        if(!$promocioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'prom_cab_observaciones'=>'required',
            'prom_cab_nombre'=>'required',
            'prom_cab_fecha_registro'=>'required',
            'prom_cab_fecha_inicio'=>'required',
            'prom_cab_fecha_fin'=>'required',
            'prom_cab_estado'=>'required',
            'tipo_promociones_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $promocioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $promocioncab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $promocioncab = PromocionesCab::find($id);
        if(!$promocioncab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'prom_cab_observaciones'=>'required',
            'prom_cab_nombre'=>'required',
            'prom_cab_fecha_registro'=>'required',
            'prom_cab_fecha_inicio'=>'required',
            'prom_cab_fecha_fin'=>'required',
            'prom_cab_estado'=>'required',
            'tipo_promociones_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $promocioncab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $promocioncab
        ],200);
    }
}
