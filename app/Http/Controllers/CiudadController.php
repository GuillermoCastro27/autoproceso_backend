<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CiudadController extends Controller
{
    public function read(){
        return DB::select('select c.*,p.pais_descrpcion from ciudades c inner join paises p on p.id = c.pais_id;');
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'ciu_descripcion'=>'required|max:200',
            'pais_id'=>'required'
        ]);
        try {
            $ciudad = Ciudad::create($datosValidados);
            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $ciudad
            ], 200);

        }catch (QueryException $e) {
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
        $ciudad = Ciudad::find($id);
        if(!$ciudad){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ciu_descripcion'=>'required|max:200',
            'pais_id'=>'required'
        ]);
        $ciudad->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $ciudad
        ],200);
    }

    public function destroy($id){
        $ciudad = Ciudad::find($id);
        if(!$ciudad){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $ciudad->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
