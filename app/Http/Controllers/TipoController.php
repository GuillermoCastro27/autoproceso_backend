<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo;

class TipoController extends Controller
{
    public function read(){
        return Tipo::all();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'tipo_descripcion'=>'required',
            'tipo_objeto'=>'required'
        ]);
        $tipo = Tipo::create($datosValidados);
        $tipo->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipo
        ],200);
    }

    public function update(Request $r, $id){
        $tipo = Tipo::find($id);
        if(!$tipo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'tipo_descripcion'=>'required',
            'tipo_objeto'=>'required'
        ]);
        $tipo->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipo
        ],200);
    }

    public function destroy($id){
        $tipo = Tipo::find($id);
        if(!$tipo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $tipo->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
