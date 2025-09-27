<?php

namespace App\Http\Controllers;

use App\Models\TipoServicio;

use Illuminate\Http\Request;

class TipoServicioController extends Controller
{
    public function read(){
        return tiposervicio::all();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'tipo_serv_nombre'=>'required'
        ]);
        $tiposervicio = TipoServicio::create($datosValidados);
        $tiposervicio->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tiposervicio
        ],200);
    }

    public function update(Request $r, $id){
        $tiposervicio = TipoServicio::find($id);
        if(!$tiposervicio){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'tipo_serv_nombre'=>'required'
        ]);
        $tiposervicio->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tiposervicio
        ],200);
    }

    public function destroy($id){
        $tiposervicio = TipoServicio::find($id);
        if(!$tiposervicio){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $tiposervicio->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
