<?php

namespace App\Http\Controllers;

use App\Models\ItemModelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemModeloController extends Controller
{
    public function read()
    {
        return DB::table('item_modelo')
            ->join('modelo', 'item_modelo.modelo_id', '=', 'modelo.id')
            ->join('items', 'item_modelo.item_id', '=', 'items.id')
            ->select('item_modelo.*', 'modelo.modelo_nom', 'items.item_decripcion')
            ->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'modelo_id'           => 'required|integer|exists:modelo,id',
            'item_id'             => 'required|integer|exists:items,id',
            'item_modelo_descrip' => 'nullable|string|max:255',
        ], [
            'modelo_id.required' => 'Debe seleccionar un modelo.',
            'modelo_id.exists'   => 'El modelo seleccionado no existe.',
            'item_id.required'   => 'Debe seleccionar un ítem.',
            'item_id.exists'     => 'El ítem seleccionado no existe.',
        ]);

        $existe = DB::table('item_modelo')
            ->where('modelo_id', $r->modelo_id)
            ->where('item_id', $r->item_id)
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Ese modelo ya está asociado a este ítem.',
                'tipo'    => 'error',
            ], 409);
        }

        $itemmodelo = ItemModelo::create([
            'modelo_id'           => $r->modelo_id,
            'item_id'             => $r->item_id,
            'item_modelo_descrip' => $r->item_modelo_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Modelo asociado al ítem con éxito',
            'tipo'     => 'success',
            'registro' => $itemmodelo,
        ]);
    }

    public function update(Request $r, $modelo_id, $item_id)
    {
        $r->validate([
            'item_modelo_descrip' => 'nullable|string|max:255',
        ]);

        DB::table('item_modelo')
            ->where('modelo_id', $modelo_id)
            ->where('item_id', $item_id)
            ->update(['item_modelo_descrip' => $r->item_modelo_descrip]);

        $registro = DB::table('item_modelo')
            ->where('modelo_id', $modelo_id)
            ->where('item_id', $item_id)
            ->first();

        return response()->json([
            'mensaje'  => 'Registro actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $registro,
        ]);
    }

    public function destroy($modelo_id, $item_id)
    {
        $eliminados = DB::table('item_modelo')
            ->where('modelo_id', $modelo_id)
            ->where('item_id', $item_id)
            ->delete();

        if (!$eliminados) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        return response()->json(['mensaje' => 'Registro eliminado con éxito', 'tipo' => 'success']);
    }
}
