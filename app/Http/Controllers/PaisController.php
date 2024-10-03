<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function read(){
        return Pais::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'pais_descrpcion'=>'required',
            'pais_gentilicio'=>'required',
            'pais_siglas'=>'required'
        ]);
        try {
            // Intentar crear el registro
            $pais = Pais::create($datosValidados);
            return response()->json([
                "mensaje" => "Registro creado con éxito",
                "tipo" => "success",
                "registro" => $pais
            ], 200);
    
        } catch (QueryException $e) {
            // Verificar si el error es por restricción de unicidad (error 23505 en PostgreSQL)
            if ($e->getCode() == 23505) {
                return response()->json([
                    "mensaje" => "Error: el Pais ya existe",
                    "tipo" => "error"
                ], 400);  // Código de error HTTP 400 (Bad Request)
            }
    
            // Manejo general de otros errores
            return response()->json([
                "mensaje" => "Error al crear el registro",
                "tipo" => "error"
            ], 500);
        }
    }
    public function update(Request $r, $id){
        $pais = Pais::find($id);
        if(!$pais){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'pais_descrpcion'=>'required',
            'pais_gentilicio'=>'required',
            'pais_siglas'=>'required'
        ]);
        $pais->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $pais
        ],200);
    }

    public function destroy($id){
        $pais = Pais::find($id);
        if(!$pais){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $pais->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
