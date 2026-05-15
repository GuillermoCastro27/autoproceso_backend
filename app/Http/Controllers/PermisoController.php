<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function read(){
        return Permiso::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'per_nombre'=>'required',
            'per_descripcion'=>'required'
        ]);
        
        $permiso = Permiso::create($datosValidados);

        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $permiso
        ],200);
    }

    public function update(Request $r, $id){
        $permiso = Permiso::find($id);
        if(!$permiso){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'per_nombre'=>'required',
            'per_descripcion'=>'required'
        ]);
        $permiso->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $permiso
        ],200);
    }

    public function destroy($id){
        $permiso = Permiso::find($id);
        if(!$permiso){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $permiso->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscar(Request $r){
        $texto = $r->input('q');

        return \DB::select("
            SELECT 
                id,
                per_nombre,
                per_descripcion
            FROM permisos
            WHERE per_nombre ILIKE ?
            ORDER BY per_nombre ASC
        ", ['%' . $texto . '%']);
    }
}
