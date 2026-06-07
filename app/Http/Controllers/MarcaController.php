<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function read(Request $r)
    {
        $q = Marca::select('id', 'marc_nom', 'mar_tipo');
        if ($r->filled('marc_nom')) {
            $q->where('marc_nom', 'ILIKE', '%' . $r->marc_nom . '%');
        }
        if ($r->filled('excluir_tipo')) {
            $q->where('mar_tipo', '!=', $r->excluir_tipo);
        }
        return response()->json($q->orderBy('marc_nom')->get());
    }

    public function store(Request $r)
    {
        $r->validate([
            'marc_nom' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('marca')
                        ->whereRaw('LOWER(marc_nom) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una marca con ese nombre.');
                    }
                },
            ],
            'mar_tipo' => 'required|string|max:50',
        ], [
            'marc_nom.required' => 'El nombre de la marca es obligatorio.',
            'marc_nom.max'      => 'El nombre no puede superar los 100 caracteres.',
            'mar_tipo.required' => 'El tipo de marca es obligatorio.',
        ]);

        $marca = Marca::create([
            'marc_nom' => $r->marc_nom,
            'mar_tipo' => $r->mar_tipo,
        ]);

        return response()->json([
            'mensaje'  => 'Marca creada con éxito',
            'tipo'     => 'success',
            'registro' => $marca,
        ]);
    }

    public function update(Request $r, $id)
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca no encontrada', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'marc_nom' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('marca')
                        ->whereRaw('LOWER(marc_nom) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra marca con ese nombre.');
                    }
                },
            ],
            'mar_tipo' => 'required|string|max:50',
        ], [
            'marc_nom.required' => 'El nombre de la marca es obligatorio.',
            'marc_nom.max'      => 'El nombre no puede superar los 100 caracteres.',
            'mar_tipo.required' => 'El tipo de marca es obligatorio.',
        ]);

        $marca->update([
            'marc_nom' => $r->marc_nom,
            'mar_tipo' => $r->mar_tipo,
        ]);

        return response()->json([
            'mensaje'  => 'Marca actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $marca,
        ]);
    }

    public function cambiarEstado($id)
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($marca->marc_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $marca->update(['marc_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Marca activada con éxito.' : 'Marca desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }

    public function buscar(Request $r)
    {
        return DB::select(
            "SELECT im.*, m.marc_nom, i.id AS item_id
             FROM items i
             JOIN item_marca im ON im.item_id = i.id
             JOIN marca m ON m.id = im.marca_id
             WHERE i.item_decripcion ILIKE ?
               AND m.marc_nom = ?",
            ['%' . $r->item_decripcion . '%', $r->marc_nom]
        );
    }

    public function buscarPorTipo(Request $r)
    {
        return response()->json(
            Marca::select('id', 'marc_nom', 'mar_tipo')
                ->where('mar_tipo', $r->input('tipo'))
                ->where('marc_nom', 'ILIKE', '%' . $r->input('texto') . '%')
                ->orderBy('marc_nom')
                ->get()
        );
    }

    public function buscarVehiculo(Request $r)
    {
        return response()->json(
            Marca::select('id', 'marc_nom', 'mar_tipo')
                ->where('mar_tipo', 'VEHICULO')
                ->where('marc_nom', 'ILIKE', '%' . $r->input('texto') . '%')
                ->orderBy('marc_nom')
                ->get()
        );
    }

    public function buscarPorTipoItem(Request $r)
    {
        return response()->json(
            Marca::select('id', 'marc_nom', 'mar_tipo')
                ->where('mar_tipo', $r->input('tipo_descripcion'))
                ->where('marc_nom', 'ILIKE', '%' . $r->input('texto', '') . '%')
                ->orderBy('marc_nom')
                ->get()
        );
    }
}
