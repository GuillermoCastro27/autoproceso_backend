<?php

namespace App\Http\Controllers;
use App\Models\TipoDescuentos;

use Illuminate\Http\Request;

class TipoDescuentosController extends Controller
{
    public function read(){
        return TipoDescuentos::all();
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'tipo_desc_descrip'=>'required',
            'tipo_desc_nombre'=>'required',
            'tipo_desc_fechaInicio'=>'required',
            'tipo_desc_fechaFin'=>'required'
        ]);
        $tipodescuentos = TipoDescuentos::create($datosValidados);
        $tipodescuentos->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipodescuentos
        ],200);
    }

    public function update(Request $r, $id){
        $tipodescuentos = TipoDescuentos::find($id);
        if(!$tipodescuentos){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'tipo_desc_descrip'=>'required',
            'tipo_desc_nombre'=>'required',
            'tipo_desc_fechaInicio'=>'required',
            'tipo_desc_fechaFin'=>'required'
        ]);
        $tipodescuentos->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipodescuentos
        ],200);
    }

    public function destroy($id){
        $tipodescuentos = TipoDescuentos::find($id);
        if(!$tipodescuentos){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $tipodescuentos->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
