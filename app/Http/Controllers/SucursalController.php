<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function read(){
        $sucursal = DB::select('
        SELECT s.*, e.emp_razon_social 
        FROM sucursal s 
        INNER JOIN empresa e ON e.id = s.empresa_id
    ');
    return response()->json($sucursal);
    }
    public function store(Request $r) {
        // Validación de los datos
        $datosValidados = $r->validate([
            'empresa_id' => 'required|integer',
            'suc_razon_social' => 'required|string',
            'suc_direccion' => 'required|string',
            'suc_telefono' => 'required|string',
            'suc_correo' => 'required|email'
        ]);
    
        try {
            // Intentar crear el registro
            $sucursal = Sucursal::create($datosValidados);
            return response()->json([
                "mensaje" => "Registro creado con éxito",
                "tipo" => "success",
                "registro" => $sucursal
            ], 200);
    
        } catch (QueryException $e) {
            // Verificar si el error es por restricción de unicidad (error 23505 en PostgreSQL)
            if ($e->getCode() == 23505) {
                return response()->json([
                    "mensaje" => "Error: el RUC ya existe",
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

    public function update(Request $r, $empresa_id){
        $sucursal = Sucursal::where('empresa_id', $empresa_id)->first();
        if(!$sucursal){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'empresa_id' => 'required|integer',
            'suc_razon_social' => 'required|string',
            'suc_direccion' => 'required|string',
            'suc_telefono' => 'required|string',
            'suc_correo' => 'required|email'
        ]);
        $sucursal->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con éxito',
            'tipo'=>'success',
            'registro'=> $sucursal
        ],200);
    }
    
    public function destroy($empresa_id){
        $sucursal = Sucursal::where('empresa_id', $empresa_id)->first();
        if(!$sucursal){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $sucursal->delete();
        return response()->json([
            'mensaje'=>'Registro eliminado con éxito',
            'tipo'=>'success',
        ],200);
    }
}