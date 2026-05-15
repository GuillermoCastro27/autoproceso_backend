<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                i.id, i.item_decripcion, i.item_costo, i.item_precio,
                t.tipo_descripcion, ti.tip_imp_nom,
                STRING_AGG(DISTINCT m.marc_nom, ', ') AS marcas,
                STRING_AGG(DISTINCT mo.modelo_nom, ', ') AS modelos
            FROM items i
            JOIN tipos t ON t.id = i.tipo_id
            JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN item_marca im ON im.item_id = i.id
            LEFT JOIN marca m ON m.id = im.marca_id
            LEFT JOIN item_modelo imo ON imo.item_id = i.id
            LEFT JOIN modelo mo ON mo.id = imo.modelo_id
            GROUP BY i.id, i.item_decripcion, i.item_costo, i.item_precio,
                     t.tipo_descripcion, ti.tip_imp_nom
            ORDER BY i.id
        ");
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
        $messages = [
            'item_costo.min'   => 'El costo no puede ser negativo.',
            'item_precio.min'  => 'El precio no puede ser negativo.',
        ];

        $datosValidados = $r->validate([
            'item_decripcion'  => 'required',
            'item_costo'       => 'required|numeric|min:0',
            'item_precio'      => 'required|numeric|min:0',
            'tipo_id'          => 'required',
            'tipo_impuesto_id' => 'required',
        ], $messages);

        $item = Item::create($datosValidados);

        $this->syncMarcas($item->id, $r->marcas ?? []);
        $this->syncModelos($item->id, $r->modelos ?? []);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $item
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $messages = [
            'item_costo.min'  => 'El costo no puede ser negativo.',
            'item_precio.min' => 'El precio no puede ser negativo.',
        ];

        $datosValidados = $r->validate([
            'item_decripcion'  => 'required',
            'item_costo'       => 'required|numeric|min:0',
            'item_precio'      => 'required|numeric|min:0',
            'tipo_id'          => 'required',
            'tipo_impuesto_id' => 'required',
        ], $messages);

        $item->update($datosValidados);

        $this->syncMarcas($id, $r->marcas ?? []);
        $this->syncModelos($id, $r->modelos ?? []);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $item
        ], 200);
    }

    public function destroy($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        DB::table('item_marca')->where('item_id', $id)->delete();
        DB::table('item_modelo')->where('item_id', $id)->delete();
        $item->delete();

        return response()->json(['mensaje' => 'Registro eliminado con éxito', 'tipo' => 'success'], 200);
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
            WHERE i.item_decripcion ILIKE '%{$r->item_decripcion}%'
            GROUP BY i.id, ti.tip_imp_nom, ti.tipo_imp_tasa, t.tipo_descripcion
        ");

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
            WHERE i.item_decripcion ILIKE '%{$r->item_decripcion}%'
            GROUP BY i.id, ti.tip_imp_nom, ti.tipo_imp_tasa, t.tipo_descripcion
        ");

        return response()->json($productos);
    }

    // -------------------------------------------------------
    private function syncMarcas($itemId, $marcas)
    {
        DB::table('item_marca')->where('item_id', $itemId)->delete();
        foreach ($marcas as $marcaId) {
            if (!$marcaId) continue;
            DB::table('item_marca')->insert([
                'item_id'           => $itemId,
                'marca_id'          => $marcaId,
                'item_marca_descrip' => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    private function syncModelos($itemId, $modelos)
    {
        DB::table('item_modelo')->where('item_id', $itemId)->delete();
        foreach ($modelos as $modeloId) {
            if (!$modeloId) continue;
            DB::table('item_modelo')->insert([
                'item_id'            => $itemId,
                'modelo_id'          => $modeloId,
                'item_modelo_descrip' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
