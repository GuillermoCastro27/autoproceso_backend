<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function read()
    {
        return DB::table('v_items')->orderBy('id')->get();
    }

    public function getMarcas($id)
    {
        return DB::table('item_marca as im')
            ->join('marca as m', 'm.id', '=', 'im.marca_id')
            ->where('im.item_id', $id)
            ->select('m.id as marca_id', 'm.marc_nom')
            ->get();
    }

    public function getModelos($id)
    {
        return DB::table('item_modelo as imo')
            ->join('modelo as mo', 'mo.id', '=', 'imo.modelo_id')
            ->where('imo.item_id', $id)
            ->select('mo.id as modelo_id', 'mo.modelo_nom')
            ->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'item_decripcion'  => ['required', 'string', 'max:200', Rule::unique('items', 'item_decripcion')->whereNull('deleted_at')],
            'item_costo'       => 'required|numeric|min:0',
            'item_precio'      => 'required|numeric|min:0',
            'tipo_id'          => 'required|integer|exists:tipos,id',
            'tipo_impuesto_id' => 'required|integer|exists:tipo_impuesto,id',
        ], [
            'item_decripcion.required'  => 'La descripción del ítem es obligatoria.',
            'item_decripcion.unique'    => 'Ya existe un ítem con esa descripción.',
            'item_costo.required'       => 'El costo es obligatorio.',
            'item_costo.numeric'        => 'El costo debe ser un valor numérico.',
            'item_costo.min'            => 'El costo no puede ser negativo.',
            'item_precio.required'      => 'El precio es obligatorio.',
            'item_precio.numeric'       => 'El precio debe ser un valor numérico.',
            'item_precio.min'           => 'El precio no puede ser negativo.',
            'tipo_id.required'          => 'Debe seleccionar un tipo.',
            'tipo_id.exists'            => 'El tipo seleccionado no existe.',
            'tipo_impuesto_id.required' => 'Debe seleccionar un tipo de impuesto.',
            'tipo_impuesto_id.exists'   => 'El tipo de impuesto seleccionado no existe.',
        ]);

        $item = Item::create([
            'item_decripcion'  => $r->item_decripcion,
            'item_costo'       => $r->item_costo,
            'item_precio'      => $r->item_precio,
            'tipo_id'          => $r->tipo_id,
            'tipo_impuesto_id' => $r->tipo_impuesto_id,
        ]);

        $this->syncMarcas($item->id, $r->marcas ?? []);
        $this->syncModelos($item->id, $r->modelos ?? []);

        return response()->json([
            'mensaje'  => 'Ítem creado con éxito',
            'tipo'     => 'success',
            'registro' => $item,
        ]);
    }

    public function update(Request $r, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['mensaje' => 'Ítem no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'item_decripcion'  => ['required', 'string', 'max:200', Rule::unique('items', 'item_decripcion')->ignore($id)->whereNull('deleted_at')],
            'item_costo'       => 'required|numeric|min:0',
            'item_precio'      => 'required|numeric|min:0',
            'tipo_id'          => 'required|integer|exists:tipos,id',
            'tipo_impuesto_id' => 'required|integer|exists:tipo_impuesto,id',
        ], [
            'item_decripcion.required'  => 'La descripción del ítem es obligatoria.',
            'item_decripcion.unique'    => 'Ya existe otro ítem con esa descripción.',
            'item_costo.required'       => 'El costo es obligatorio.',
            'item_costo.min'            => 'El costo no puede ser negativo.',
            'item_precio.required'      => 'El precio es obligatorio.',
            'item_precio.min'           => 'El precio no puede ser negativo.',
            'tipo_id.required'          => 'Debe seleccionar un tipo.',
            'tipo_impuesto_id.required' => 'Debe seleccionar un tipo de impuesto.',
        ]);

        $item->update([
            'item_decripcion'  => $r->item_decripcion,
            'item_costo'       => $r->item_costo,
            'item_precio'      => $r->item_precio,
            'tipo_id'          => $r->tipo_id,
            'tipo_impuesto_id' => $r->tipo_impuesto_id,
        ]);

        $this->syncMarcas($id, $r->marcas ?? []);
        $this->syncModelos($id, $r->modelos ?? []);

        return response()->json([
            'mensaje'  => 'Ítem actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['mensaje' => 'Ítem no encontrado', 'tipo' => 'error'], 404);
        }

        DB::table('item_marca')->where('item_id', $id)->delete();
        DB::table('item_modelo')->where('item_id', $id)->delete();

        try {
            $item->delete();
            return response()->json(['mensaje' => 'Ítem eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el ítem porque está siendo utilizado en compras, ventas u otros registros.',
                'tipo'    => 'error',
            ], 409);
        }
    }

    public function buscar(Request $r)
    {
        $productos = DB::select("
            SELECT
                i.*,
                ti.tip_imp_nom,
                ti.tipo_imp_tasa,
                i.item_costo,
                i.id AS item_id,
                COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
            FROM items i
            JOIN tipos t ON t.id = i.tipo_id
            LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN stock s ON s.item_id = i.id
            WHERE i.deleted_at IS NULL AND i.item_decripcion ILIKE ?
            GROUP BY i.id, ti.tip_imp_nom, ti.tipo_imp_tasa, t.tipo_descripcion
        ", ['%' . $r->item_decripcion . '%']);

        return response()->json($productos);
    }

    public function buscarItem(Request $r)
    {
        $productos = DB::select("
            SELECT
                i.*,
                ti.tip_imp_nom,
                ti.tipo_imp_tasa,
                i.item_precio AS item_costo,
                i.id AS item_id,
                COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
            FROM items i
            JOIN tipos t ON t.id = i.tipo_id
            LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN stock s ON s.item_id = i.id
            WHERE i.deleted_at IS NULL AND i.item_decripcion ILIKE ?
            GROUP BY i.id, ti.tip_imp_nom, ti.tipo_imp_tasa, t.tipo_descripcion
        ", ['%' . $r->item_decripcion . '%']);

        return response()->json($productos);
    }

    private function syncMarcas($itemId, $marcas)
    {
        DB::table('item_marca')->where('item_id', $itemId)->delete();
        foreach ($marcas as $marcaId) {
            if (!$marcaId) continue;
            DB::table('item_marca')->insert([
                'item_id'            => $itemId,
                'marca_id'           => $marcaId,
                'item_marca_descrip' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    private function syncModelos($itemId, $modelos)
    {
        DB::table('item_modelo')->where('item_id', $itemId)->delete();
        foreach ($modelos as $modeloId) {
            if (!$modeloId) continue;
            DB::table('item_modelo')->insert([
                'item_id'             => $itemId,
                'modelo_id'           => $modeloId,
                'item_modelo_descrip' => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
