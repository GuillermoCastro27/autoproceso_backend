<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;

class CajaController extends Controller
{
    public function read(){
        return Caja::all();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'caja_descripcion'=>'required'
        ]);
        $caja = Caja::create($datosValidados);
        $caja->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $caja
        ],200);
    }

    public function update(Request $r, $id){
        $caja = Caja::find($id);
        if(!$caja){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'caja_descripcion'=>'required'
        ]);
        $caja->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $caja
        ],200);
    }

    public function destroy($id){
        $caja = Caja::find($id);
        if(!$caja){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $caja->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
