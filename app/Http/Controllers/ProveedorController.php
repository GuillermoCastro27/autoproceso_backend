<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function read(){
        return DB::table('proveedores')
        ->join('ciudades', 'proveedores.ciudad_id', '=', 'ciudades.id')
        ->join('nacionalidad', 'proveedores.nacionalidad_id', '=', 'nacionalidad.id')
        ->join('paises', 'proveedores.pais_id', '=', 'paises.id')
        ->select('proveedores.*', 'paises.pais_descrpcion as pais_descrpcion',
        'ciudades.ciu_descripcion as ciu_descripcion',
        'nacionalidad.nacio_descripcion as nacio_descripcion')
        ->get();
    }
    public function store(Request $r) {
        // Validación de los datos
        $datosValidados = $r->validate([
            'prov_razonsocial' => 'required',
            'prov_ruc' => 'required',
            'prov_direccion' => 'required',
            'prov_telefono' => 'required',
            'prov_correo' => 'required',
            'pais_id' => 'required',
            'ciudad_id' => 'required',
            'nacionalidad_id' => 'required'
        ]);
    
        try {
            // Intentar crear el registro
            $proveedor = Proveedor::create($datosValidados);
            return response()->json([
                "mensaje" => "Registro creado con éxito",
                "tipo" => "success",
                "registro" => $proveedor
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
    
    public function update(Request $r, $id){
        $proveedor = Proveedor::find($id);
        if(!$proveedor){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'prov_razonsocial'=>'required',
            'prov_ruc'=>'required',
            'prov_direccion'=>'required',
            'prov_telefono'=>'required',
            'prov_correo'=>'required',
            'pais_id' => 'required',
            'ciudad_id'=>'required',
            'nacionalidad_id'=>'required'
        ]);
        $proveedor->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $proveedor
        ],200);
    }

    public function destroy($id){
        $proveedor = Proveedor::find($id);
        if(!$proveedor){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $proveedor->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscar(Request $r){
        return DB::select(
        "select p.*,p.*,
        p.id as proveedor_id from proveedores p where prov_razonsocial ilike '%{$r->prov_razonsocial}%' or prov_ruc ilike '%{$r->prov_razonsocial}%'");
    }
}
