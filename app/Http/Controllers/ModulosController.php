<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class ModulosController extends Controller
{
    public function read(){
        return Modulo::all();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'mod_nombre'=>'required',
            'mod_descripcion'=>'required',
            'mod_estado'=>'required'
        ]);

        $modulo = Modulo::create($datosValidados);

        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $modulo
        ],200);
    }

    public function update(Request $r, $id){
        $modulo = Modulo::find($id);

        if(!$modulo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }

        $datosValidados = $r->validate([
            'mod_nombre'=>'required',
            'mod_descripcion'=>'required',
            'mod_estado'=>'required'
        ]);

        $modulo->update($datosValidados);

        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $modulo
        ],200);
    }

    public function destroy($id){
        $modulo = Modulo::find($id);

        if(!$modulo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }

        $modulo->delete();

        return response()->json([
            'mensaje'=>'Registro eliminado con exito',
            'tipo'=>'success'
        ],200);
    }
}