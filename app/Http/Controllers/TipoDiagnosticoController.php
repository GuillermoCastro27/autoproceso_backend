<?php

namespace App\Http\Controllers;
use App\Models\TipoDiagnostico;

use Illuminate\Http\Request;

class TipoDiagnosticoController extends Controller
{
     public function read()
    {
        return response()->json(
            TipoDiagnostico::select('id as tipo_diagnostico_id', 'tipo_diag_nombre as tipo_diag_nombre', 'tipo_diag_descrip as tipo_diag_descrip')->get()
        );
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'tipo_diag_nombre' => 'required|string|max:100|unique:tipo_diagnostico,tipo_diag_nombre',
            'tipo_diag_descrip' => 'required|string|max:255',
        ], [
            'tipo_diag_nombre.required' => 'El campo nombre es obligatorio.',
            'tipo_diag_nombre.unique' => 'El tipo de diagnóstico ya existe.',
            'tipo_diag_descrip.required' => 'El campo descripción es obligatorio.',
            'tipo_diag_descrip.unique' => 'El campo descripción ya existe.',
        ]);
        $datosValidados = $r->validate([
            'tipo_diag_nombre'=>'required',
            'tipo_diag_descrip'=>'required'
        ]);
        $tipodiagnostico = TipoDiagnostico::create($datosValidados);
        $tipodiagnostico->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipodiagnostico
        ],200);
    }

    public function update(Request $r, $id){
        $tipodiagnostico = TipoDiagnostico::find($id);
        if(!$tipodiagnostico){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'tipo_diag_nombre' => 'required|string|max:100|unique:tipo_diagnostico,tipo_diag_nombre,' . $id,
            'tipo_diag_descrip' => 'required|string|max:255|unique:tipo_diagnostico,tipo_diag_descrip,' . $id
        ], [
            'tipo_diag_nombre.required' => 'El campo nombre es obligatorio.',
            'tipo_diag_nombre.unique' => 'El tipo de diagnóstico ya existe.',
            'tipo_diag_descrip.required' => 'El campo descripción es obligatorio.',
            'tipo_diag_descrip.unique' => 'El campo descripción ya existe.',
        ]);
        $datosValidados = $r->validate([
            'tipo_diag_nombre'=>'required',
            'tipo_diag_descrip'=>'required'
        ]);
        $tipodiagnostico->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipodiagnostico
        ],200);
    }

    public function destroy($id){
        $tipodiagnostico = TipoDiagnostico::find($id);
        if(!$tipodiagnostico){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $tipodiagnostico->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
