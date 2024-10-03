<?php

namespace App\Http\Controllers;
use App\Models\TipoImpuesto;

use Illuminate\Http\Request;

class TipoImpuestoController extends Controller
{
    public function read(){
        return TipoImpuesto::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'tip_imp_nom'=>'required',
            'tipo_imp_tasa'=>'required'
        ]);
        try {
            $tipoimpuesto = TipoImpuesto::create($datosValidados);
            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $tipoimpuesto
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
        $tipoimpuesto = TipoImpuesto::find($id);
        if(!$tipoimpuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'tip_imp_nom'=>'required',
            'tipo_imp_tasa'=>'required'
        ]);
        $tipoimpuesto->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $tipoimpuesto
        ],200);
    }
    public function destroy($id){
        $tipoimpuesto = TipoImpuesto::find($id);
        if(!$tipoimpuesto){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $tipoimpuesto->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscar(Request $r){
        $query = $r->input('query');
        return TipoImpuesto::where('tip_imp_nom','ilike',"%{query}")
            ->orWhere('tipo_imp_tasa','ilike',"%{query}")
            ->get();
    }
}
