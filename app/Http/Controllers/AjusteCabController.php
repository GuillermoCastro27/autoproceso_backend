<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteCab;


class AjusteCabController extends Controller
{
    public function read() {
        return DB::select("SELECT 
            ac.id,
            TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ajus_cab_fecha,
            ac.ajus_cab_estado,
            ac.tipo_ajuste,
            ac.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            ac.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            ac.user_id,
            ac.motivo_ajuste_id,
            ma.descripcion AS descripcion,
            ac.created_at,
            ac.updated_at,
            u.name
        FROM ajuste_cab ac
        JOIN sucursal s ON s.empresa_id = ac.sucursal_id
        JOIN empresa e ON e.id = ac.empresa_id
        JOIN users u ON u.id = ac.user_id
        JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id;");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'ajus_cab_fecha'=>'required',
            'ajus_cab_estado'=>'required',
            'tipo_ajuste'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'motivo_ajuste_id'=>'required'
        ]);
        $ajustecab = AjusteCab::create($datosValidados);
        $ajustecab->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $ajustecab
        ],200);
    }
    public function update(Request $r, $id){
        $ajustecab = AjusteCab::find($id);
        if(!$ajustecab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ajus_cab_fecha'=>'required',
            'ajus_cab_estado'=>'required',
            'tipo_ajuste'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'motivo_ajuste_id'=>'required'
        ]);
        $ajustecab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $ajustecab
        ],200);
    }
    public function anular(Request $r, $id){
        $ajustecab = AjusteCab::find($id);
        if(!$ajustecab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ajus_cab_fecha'=>'required',
            'ajus_cab_estado'=>'required',
            'tipo_ajuste'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'motivo_ajuste_id'=>'required'
        ]);
        $ajustecab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $ajustecab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $ajustecab = AjusteCab::find($id);
        if(!$ajustecab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ajus_cab_fecha'=>'required',
            'ajus_cab_estado'=>'required',
            'tipo_ajuste'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required',
            'motivo_ajuste_id'=>'required'
        ]);
        $ajustecab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $ajustecab
        ],200);
    }
}
