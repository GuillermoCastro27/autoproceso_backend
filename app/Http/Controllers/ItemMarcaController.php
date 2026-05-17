<?php

namespace App\Http\Controllers;

use App\Models\ItemMarca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemMarcaController extends Controller
{
    public function read()
    {
        return DB::table('item_marca')
            ->join('marca', 'item_marca.marca_id', '=', 'marca.id')
            ->join('items', 'item_marca.item_id', '=', 'items.id')
            ->select('item_marca.*', 'marca.marc_nom', 'items.item_decripcion')
            ->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'marca_id'           => 'required|integer|exists:marca,id',
            'item_id'            => 'required|integer|exists:items,id',
            'item_marca_descrip' => 'nullable|string|max:255',
        ], [
            'marca_id.required' => 'Debe seleccionar una marca.',
            'marca_id.exists'   => 'La marca seleccionada no existe.',
            'item_id.required'  => 'Debe seleccionar un ítem.',
            'item_id.exists'    => 'El ítem seleccionado no existe.',
        ]);

        $existe = DB::table('item_marca')
            ->where('marca_id', $r->marca_id)
            ->where('item_id', $r->item_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Esa marca ya está asociada a este ítem.',
                'tipo'    => 'error',
            ], 409);
        }

        $itemmarca = ItemMarca::create([
            'marca_id'           => $r->marca_id,
            'item_id'            => $r->item_id,
            'item_marca_descrip' => $r->item_marca_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Marca asociada al ítem con éxito',
            'tipo'     => 'success',
            'registro' => $itemmarca,
        ]);
    }

    public function update(Request $r, $marca_id, $item_id)
    {
        $r->validate([
            'item_marca_descrip' => 'nullable|string|max:255',
        ]);

        DB::table('item_marca')
            ->where('marca_id', $marca_id)
            ->where('item_id', $item_id)
            ->update(['item_marca_descrip' => $r->item_marca_descrip]);

        $registro = DB::table('item_marca')
            ->where('marca_id', $marca_id)
            ->where('item_id', $item_id)
            ->first();

        return response()->json([
            'mensaje'  => 'Registro actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $registro,
        ]);
    }

    public function destroy($marca_id, $item_id)
    {
        $eliminados = DB::table('item_marca')
            ->where('marca_id', $marca_id)
            ->where('item_id', $item_id)
            ->delete();

        if (!$eliminados) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        return response()->json(['mensaje' => 'Registro eliminado con éxito', 'tipo' => 'success']);
    }
}
