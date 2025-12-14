<?php

namespace App\Http\Controllers;
use App\Models\Marca;

use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function read()
    {
        return response()->json(
            Marca::select('id', 'marc_nom as marc_nom', 'mar_tipo as mar_tipo')->get()
        );
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'marc_nom'=>'required',
            'mar_tipo'=>'required'
        ]);
        $marca = Marca::create($datosValidados);
        $marca->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $marca
        ],200);
    }
    public function update(Request $r, $id){
        $marca = Marca::find($id);
        if(!$marca){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'marc_nom'=>'required',
            'mar_tipo'=>'required'
        ]);
        $marca->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $marca
        ],200);
    }
    public function destroy($id){
        $marca = Marca::find($id);
        if(!$marca){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $marca->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("select im.*, m.marc_nom, i.id AS item_id
        FROM items i
        JOIN item_marca im ON im.item_id = i.id
        JOIN marca m ON m.id = im.marca_id
        WHERE i.item_decripcion ILIKE '%$r->item_decripcion%'
        AND m.marc_nom = '$r->marc_nom'");
    }
    public function buscarPorTipo(Request $r)
{
    $texto = $r->input('texto');
    $tipo = $r->input('tipo');

    $resultado = Marca::select('id', 'marc_nom', 'mar_tipo')
        ->where('mar_tipo', '=', $tipo)
        ->where('marc_nom', 'ILIKE', "%$texto%")
        ->orderBy('marc_nom')
        ->get();

    return response()->json($resultado);
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
public function buscarVehiculo(Request $r)
{
    $texto = $r->input('texto'); // texto que escribe el usuario

    $resultado = Marca::select('id', 'marc_nom', 'mar_tipo')
        ->where('mar_tipo', 'VEHICULO')
        ->where('marc_nom', 'ILIKE', "%$texto%")
        ->orderBy('marc_nom')
        ->get();

    return response()->json($resultado);
}
public function buscarPorTipoItem(Request $r)
{
    // texto que escribe el usuario en el campo Marca del CRUD de Items
    $texto = $r->input('texto', '');

    // tipo de ítem seleccionado (PRODUCTO, LUBRICANTES, etc.)
    $tipoItem = $r->input('tipo_descripcion');

    $resultado = Marca::select('id', 'marc_nom', 'mar_tipo')
        ->where('mar_tipo', $tipoItem)                  // filtra por tipo
        ->where('marc_nom', 'ILIKE', "%$texto%")        // filtra por nombre
        ->orderBy('marc_nom')
        ->get();

    return response()->json($resultado);
}

}