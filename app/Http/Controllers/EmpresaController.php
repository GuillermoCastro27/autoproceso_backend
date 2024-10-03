<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function read(){
        return Empresa::all();
    }
    public function store(Request $r) {
        // Validación de los datos
        $datosValidados = $r->validate([
            'emp_razon_social' => 'required',
            'emp_direccion' => 'required',
            'emp_telefono' => 'required',
            'emp_correo' => 'required'
        ]);
    
        try {
            // Intentar crear el registro
            $empresa = Empresa::create($datosValidados);
            return response()->json([
                "mensaje" => "Registro creado con éxito",
                "tipo" => "success",
                "registro" => $empresa
            ], 200);
    
        } catch (QueryException $e) {
            // Verificar si el error es por restricción de unicidad (error 23505 en PostgreSQL)
            if ($e->getCode() == 23505) {
                return response()->json([
                    "mensaje" => "Error: la empresa ya existe",
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
        $empresa = Empresa::find($id);
        if(!$empresa){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'emp_razon_social' => 'required',
            'emp_direccion' => 'required',
            'emp_telefono' => 'required',
            'emp_correo' => 'required'
        ]);
        $empresa->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $empresa
        ],200);
    }

    public function destroy($id){
        $empresa = Empresa::find($id);
        if(!$empresa){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $empresa->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
