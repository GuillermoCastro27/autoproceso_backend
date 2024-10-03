<?php

namespace App\Http\Controllers;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function read(){
        return DB::table('funcionario')
             ->join('paises', 'funcionario.pais_id', '=', 'paises.id')
             ->join('ciudades', 'funcionario.ciudad_id', '=', 'ciudades.id')
             ->join('nacionalidad', 'funcionario.nacionalidad_id', '=', 'nacionalidad.id')
             ->select('funcionario.*', 'ciudades.ciu_descripcion as ciu_descripcion', 'nacionalidad.nacio_descripcion as nacio_descripcion', 'paises.pais_descrpcion as pais_descrpcion')
             ->get();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'fun_nom'=>'required',
            'fun_apellido'=>'required',
            'fun_direccion'=>'required',
            'fun_telefono'=>'required',
            'fun_correo'=>'required',
            'fun_ci'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'nacionalidad_id'=>'required'
        ]);
        $funcionario = Funcionario::create($datosValidados);
        $funcionario->save();
        return response()->json([
            "mensaje"=>"Registro creado con exito",
            "tipo"=>"success",
            "registro"=>$funcionario
        ],200);
    }
    public function update(Request $r, $id){
        $funcionario = Funcionario::find($id);
        if(!$funcionario){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'fun_nom'=>'required',
            'fun_apellido'=>'required',
            'fun_direccion'=>'required',
            'fun_telefono'=>'required',
            'fun_correo'=>'required',
            'fun_ci'=>'required',
            'pais_id'=>'required',
            'ciudad_id'=>'required',
            'nacionalidad_id'=>'required'
        ]);
        $funcionario->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $funcionario
        ],200);
    }

    public function destroy($id){
        $funcionario = Funcionario::find($id);
        if(!$funcionario){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $funcionario->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
}
