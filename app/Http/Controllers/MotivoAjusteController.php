<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MotivoAjuste;
use Illuminate\Support\Facades\DB;

class MotivoAjusteController extends Controller
{
    public function read() {
        return DB::select("SELECT id, descripcion, tipo_ajuste, created_at, updated_at FROM motivo_ajuste");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'descripcion'=>'required',
            'tipo_ajuste'=>'required'
        ]);
        $motivoajuste = MotivoAjuste::create($datosValidados);
        $motivoajuste->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $motivoajuste
        ],200);
    }
    public function update(Request $r, $id){
        $motivoajuste = MotivoAjuste::find($id);
        if(!$motivoajuste){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'descripcion'=>'required',
            'tipo_ajuste'=>'required'
        ]);
        $motivoajuste->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $motivoajuste
        ],200);
    }
    public function destroy($id){
        $motivoajuste = MotivoAjuste::find($id);
        if(!$motivoajuste){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $motivoajuste->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    
}
