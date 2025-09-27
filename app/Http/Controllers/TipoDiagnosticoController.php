<?php

namespace App\Http\Controllers;
use App\Models\TipoDiagnostico;

use Illuminate\Http\Request;

class TipoDiagnosticoController extends Controller
{
    public function read(){
        return TipoDiagnostico::all();
    }

    public function store(Request $r){
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
