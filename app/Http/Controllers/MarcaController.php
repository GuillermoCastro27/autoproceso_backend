<?php

namespace App\Http\Controllers;
use App\Models\Marca;

use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function read(){
        return Marca::all();
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'marc_nom'=>'required'
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
            'marc_nom'=>'required'
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
}
