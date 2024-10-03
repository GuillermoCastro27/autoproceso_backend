<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nacionalidad;

class NacionalidadController extends Controller
{
    public function read(){
        return Nacionalidad::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'nacio_descripcion' => 'required'
        ]);

        try {
            $nacionalidad = Nacionalidad::create($datosValidados);
            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $nacionalidad
            ], 200);

        } catch (QueryException $e) {
            // Verificar si el error es por restricción de unicidad (error 23505 en PostgreSQL)
            if ($e->getCode() == 23505) {
                return response()->json([
                    'mensaje' => 'Error: ese registro ya existe',
                    'tipo' => 'error'
                ], 400);  // Puedes usar un código de error HTTP 400 (Bad Request)
            }

            // Manejo general de otros errores de base de datos
            return response()->json([
                'mensaje' => 'Error al crear el registro',
                'tipo' => 'error'
            ], 500);
        }
    }
    public function update(Request $r, $id){
        $nacionalidad = Nacionalidad::find($id);
        if(!$nacionalidad){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'nacio_descripcion'=>'required'
        ]);
        $nacionalidad->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $nacionalidad
        ],200);
    }
    public function destroy($id){
        $nacionalidad = Nacionalidad::find($id);
        if(!$nacionalidad){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $nacionalidad->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
