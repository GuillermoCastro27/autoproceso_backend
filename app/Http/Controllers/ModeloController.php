<?php

namespace App\Http\Controllers;
use App\Models\Modelo;

use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function read(){
        return Modelo::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'modelo_nom'=>'required'
        ]);
        $modelo = Modelo::create($datosValidados);
        $modelo->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $modelo
        ],200);
    }
    public function update(Request $r, $id){
        $modelo = Modelo::find($id);
        if(!$modelo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'modelo_nom'=>'required'
        ]);
        $modelo->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $modelo
        ],200);
    }
    public function destroy($id){
        $modelo = Modelo::find($id);
        if(!$modelo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $modelo->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
