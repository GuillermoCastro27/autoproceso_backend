<?php

namespace App\Http\Controllers;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function read(){
        return DB::table('clientes')
             ->join('paises', 'clientes.pais_id', '=', 'paises.id')
             ->join('ciudades', 'clientes.ciudad_id', '=', 'ciudades.id')
             ->join('nacionalidad', 'clientes.nacionalidad_id', '=', 'nacionalidad.id')
             ->select('clientes.*', 'ciudades.ciu_descripcion as ciu_descripcion',
            'nacionalidad.nacio_descripcion as nacio_descripcion',
            'paises.pais_descrpcion as pais_descrpcion')
             ->get();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'cli_nombre'=>'required',
            'cli_apellido'=>'required',
            'cli_ruc'=>'required',
            'cli_direccion'=>'required',
            'cli_telefono'=>'required',
            'cli_correo'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'nacionalidad_id'=>'required'
        ]);
        $cliente = Cliente::create($datosValidados);
        $cliente->save();
        return response()->json([
            "mensaje"=>"Registro creado con exito",
            "tipo"=>"success",
            "registro"=>$cliente
        ],200);
    }
    public function update(Request $r, $id){
        $cliente = Cliente::find($id);
        if(!$cliente){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'cli_nombre'=>'required',
            'cli_apellido'=>'required',
            'cli_ruc'=>'required',
            'cli_direccion'=>'required',
            'cli_telefono'=>'required',
            'cli_correo'=>'required'
        ]);
        $cliente->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $cliente
        ],200);
    }

    public function destroy($id){
        $cliente = Cliente::find($id);
        if(!$cliente){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $cliente->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscar(Request $r){
        return DB::select(
        "select c.*,c.*,
        c.id as clientes_id from clientes c where cli_nombre ilike '%{$r->cli_nombre}%' or cli_ruc ilike '%{$r->cli_nombre}%'");
    }
}
