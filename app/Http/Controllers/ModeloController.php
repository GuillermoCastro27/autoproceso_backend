<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Modelo;

use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public function read()
{
    $data = Modelo::select(
        'modelo.id',
        'modelo.modelo_nom',
        'modelo.modelo_tipo',
        DB::raw("COALESCE(modelo.modelo_año::text, 'N/A') AS modelo_año"),
        'modelo.marca_id',
        'marca.marc_nom AS marc_nom'
    )
    ->join('marca', 'marca.id', '=', 'modelo.marca_id')
    ->get();

    return response()->json($data);
}
    public function store(Request $r){
        $datosValidados = $r->validate([
            'modelo_nom'=>'required',
            'modelo_tipo'=>'required',
            'modelo_año'=>'required',
            'marca_id'=>'required'
        ]);
        $modelo = Modelo::create($datosValidados);
        $modelo->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $modelo
        ],200);
    }
    public function update(Request $r, $id){
        $modelo = Modelo::find($id);
        if(!$modelo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'modelo_nom'=>'required',
            'modelo_tipo'=>'required',
            'modelo_año'=>'required',
            'marca_id'=>'required'
        ]);
        $modelo->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $modelo
        ],200);
    }
    public function destroy($id){
        $modelo = Modelo::find($id);
        if(!$modelo){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $modelo->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscarPorMarca(Request $r)
{
    $marca_id = $r->input('marca_id');
    $texto = $r->input('texto');

    $resultado = Modelo::select('id', 'modelo_nom', 'modelo_año')
        ->where('marca_id', $marca_id)
        ->where('modelo_nom', 'ILIKE', "%$texto%")
        ->orderBy('modelo_nom')
        ->get();

    return response()->json($resultado);
}
public function buscarModelosItem(Request $r)
{
    $marca_id = $r->input('marca_id');
    $texto = $r->input('texto', '');

    $resultado = Modelo::select('id', 'modelo_nom')   // 👈 QUITAMOS modelo_año
        ->where('marca_id', $marca_id)
        ->where('modelo_nom', 'ILIKE', "%$texto%")
        ->orderBy('modelo_nom')
        ->get();

    return response()->json($resultado);
}

}
