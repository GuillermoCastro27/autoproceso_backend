<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->select('mo.id as modelo_id', 'mo.modelo_nom', 'mo.modelo_año', 'mo.marca_id')
            ->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'item_decripcion'  => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('items')
                        ->whereRaw('LOWER(item_decripcion) = LOWER(?)', [trim($value)])
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un ítem con esa descripción.');
                    }
                },
            ],
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

        $this->syncMarcas($item->id,  $this->toArray($r->marcas));
        $this->syncModelos($item->id, $this->toArray($r->modelos));

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
            'item_decripcion'  => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('items')
                        ->whereRaw('LOWER(item_decripcion) = LOWER(?)', [trim($value)])
                        ->whereNull('deleted_at')
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro ítem con esa descripción.');
                    }
                },
            ],
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

        $this->syncMarcas($id,  $this->toArray($r->marcas));
        $this->syncModelos($id, $this->toArray($r->modelos));

        return response()->json([
            'mensaje'  => 'Ítem actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $item,
        ]);
    }

    public function cambiarEstado($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['mensaje' => 'Ítem no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = $item->item_estado === 'activo' ? 'inactivo' : 'activo';
        $item->update(['item_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Ítem activado con éxito.' : 'Ítem desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }

    public function buscar(Request $r)
    {
        $depositoId      = $r->input('deposito_id');
        $tipoDescripcion = $r->input('tipo_descripcion'); // filtro opcional: INSUMO, PRODUCTO, etc.

        $tipoFiltro      = $tipoDescripcion ? "AND UPPER(t.tipo_descripcion) = UPPER(?)" : "";

        if ($depositoId) {
            $params = $tipoDescripcion
                ? [$depositoId, '%' . $r->item_decripcion . '%', $tipoDescripcion]
                : [$depositoId, '%' . $r->item_decripcion . '%'];

            $productos = DB::select("
                SELECT
                    i.*,
                    t.tipo_descripcion,
                    ti.tip_imp_nom,
                    ti.tipo_imp_tasa,
                    i.item_costo,
                    i.id AS item_id,
                    COALESCE(s.cantidad, 0) AS cantidad_disponible
                FROM items i
                JOIN tipos t ON t.id = i.tipo_id
                LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
                JOIN stock s ON s.item_id = i.id AND s.deposito_id = ?
                WHERE i.deleted_at IS NULL
                  AND i.item_decripcion ILIKE ?
                  AND s.cantidad > 0
                  $tipoFiltro
                GROUP BY i.id, t.tipo_descripcion, ti.tip_imp_nom, ti.tipo_imp_tasa, s.cantidad
            ", $params);
        } else {
            $params = $tipoDescripcion
                ? ['%' . $r->item_decripcion . '%', $tipoDescripcion]
                : ['%' . $r->item_decripcion . '%'];

            $productos = DB::select("
                SELECT
                    i.*,
                    t.tipo_descripcion,
                    ti.tip_imp_nom,
                    ti.tipo_imp_tasa,
                    i.item_costo,
                    i.id AS item_id,
                    COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
                FROM items i
                JOIN tipos t ON t.id = i.tipo_id
                LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
                LEFT JOIN stock s ON s.item_id = i.id
                WHERE i.deleted_at IS NULL
                  AND i.item_decripcion ILIKE ?
                  $tipoFiltro
                GROUP BY i.id, t.tipo_descripcion, ti.tip_imp_nom, ti.tipo_imp_tasa
            ", $params);
        }

        return response()->json($productos);
    }

    public function buscarItem(Request $r)
    {
        $tipoDescripcion = $r->input('tipo_descripcion');
        $tipoFiltro      = $tipoDescripcion ? "AND UPPER(t.tipo_descripcion) = UPPER(?)" : "";

        $params = $tipoDescripcion
            ? ['%' . $r->item_decripcion . '%', $tipoDescripcion]
            : ['%' . $r->item_decripcion . '%'];

        $productos = DB::select("
            SELECT
                i.*,
                t.tipo_descripcion,
                ti.tip_imp_nom,
                ti.tipo_imp_tasa,
                i.item_precio AS item_costo,
                i.id AS item_id,
                COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
            FROM items i
            JOIN tipos t ON t.id = i.tipo_id
            LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN stock s ON s.item_id = i.id
            WHERE i.deleted_at IS NULL
              AND i.item_decripcion ILIKE ?
              $tipoFiltro
            GROUP BY i.id, t.tipo_descripcion, ti.tip_imp_nom, ti.tipo_imp_tasa
        ", $params);

        return response()->json($productos);
    }

    private function toArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_null($value) || $value === '') return [];
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : (trim($value) !== '' ? [$value] : []);
        }
        // int, float u otro escalar → envolverlo en array
        return [$value];
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
