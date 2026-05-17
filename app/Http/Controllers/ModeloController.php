<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModeloController extends Controller
{
    public function read(Request $r)
    {
        $q = Modelo::select(
            'modelo.id',
            'modelo.modelo_nom',
            'modelo.modelo_tipo',
            DB::raw("COALESCE(modelo.modelo_año::text, 'N/A') AS modelo_año"),
            'modelo.marca_id',
            'marca.marc_nom AS marc_nom'
        )
        ->join('marca', 'marca.id', '=', 'modelo.marca_id');

        if ($r->filled('modelo_nom')) {
            $q->where('modelo.modelo_nom', 'ILIKE', '%' . $r->modelo_nom . '%');
        }
        if ($r->filled('excluir_tipo')) {
            $q->where('modelo.modelo_tipo', '!=', $r->excluir_tipo);
        }

        return response()->json($q->orderBy('modelo.modelo_nom')->get());
    }

    public function store(Request $r)
    {
        $anoActual = (int) date('Y');

        $r->validate([
            'modelo_nom'  => [
                'required', 'string', 'max:100',
                Rule::unique('modelo', 'modelo_nom')->where(fn($q) => $q->where('marca_id', $r->marca_id)),
            ],
            'modelo_tipo' => 'required|string|max:50',
            'modelo_año'  => "nullable|integer|min:1900|max:{$anoActual}",
            'marca_id'    => 'required|integer|exists:marca,id',
        ], [
            'modelo_nom.required' => 'El nombre del modelo es obligatorio.',
            'modelo_nom.max'      => 'El nombre no puede superar los 100 caracteres.',
            'modelo_nom.unique'   => 'Ya existe un modelo con ese nombre para la marca seleccionada.',
            'modelo_tipo.required'=> 'El tipo de modelo es obligatorio.',
            'modelo_año.integer'  => 'El año debe ser un número entero.',
            'modelo_año.min'      => 'El año no puede ser anterior a 1900.',
            'modelo_año.max'      => "El año no puede ser posterior a {$anoActual}.",
            'marca_id.required'   => 'Debe seleccionar una marca.',
            'marca_id.exists'     => 'La marca seleccionada no existe.',
        ]);

        $modelo = Modelo::create([
            'modelo_nom'  => $r->modelo_nom,
            'modelo_tipo' => $r->modelo_tipo,
            'modelo_año'  => $r->modelo_año,
            'marca_id'    => $r->marca_id,
        ]);

        return response()->json([
            'mensaje'  => 'Modelo creado con éxito',
            'tipo'     => 'success',
            'registro' => $modelo,
        ]);
    }

    public function update(Request $r, $id)
    {
        $modelo = Modelo::find($id);
        if (!$modelo) {
            return response()->json(['mensaje' => 'Modelo no encontrado', 'tipo' => 'error'], 404);
        }

        $anoActual = (int) date('Y');

        $r->validate([
            'modelo_nom'  => [
                'required', 'string', 'max:100',
                Rule::unique('modelo', 'modelo_nom')
                    ->where(fn($q) => $q->where('marca_id', $r->marca_id))
                    ->ignore($id),
            ],
            'modelo_tipo' => 'required|string|max:50',
            'modelo_año'  => "nullable|integer|min:1900|max:{$anoActual}",
            'marca_id'    => 'required|integer|exists:marca,id',
        ], [
            'modelo_nom.required' => 'El nombre del modelo es obligatorio.',
            'modelo_nom.max'      => 'El nombre no puede superar los 100 caracteres.',
            'modelo_nom.unique'   => 'Ya existe otro modelo con ese nombre para la marca seleccionada.',
            'modelo_tipo.required'=> 'El tipo de modelo es obligatorio.',
            'modelo_año.integer'  => 'El año debe ser un número entero.',
            'modelo_año.min'      => 'El año no puede ser anterior a 1900.',
            'modelo_año.max'      => "El año no puede ser posterior a {$anoActual}.",
            'marca_id.required'   => 'Debe seleccionar una marca.',
            'marca_id.exists'     => 'La marca seleccionada no existe.',
        ]);

        $modelo->update([
            'modelo_nom'  => $r->modelo_nom,
            'modelo_tipo' => $r->modelo_tipo,
            'modelo_año'  => $r->modelo_año,
            'marca_id'    => $r->marca_id,
        ]);

        return response()->json([
            'mensaje'  => 'Modelo actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $modelo,
        ]);
    }

    public function destroy($id)
    {
        $modelo = Modelo::find($id);
        if (!$modelo) {
            return response()->json(['mensaje' => 'Modelo no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $modelo->delete();
            return response()->json(['mensaje' => 'Modelo eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el modelo porque está siendo utilizado en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }

    public function buscarPorMarca(Request $r)
    {
        return response()->json(
            Modelo::select('id', 'modelo_nom', 'modelo_año')
                ->where('marca_id', $r->input('marca_id'))
                ->where('modelo_nom', 'ILIKE', '%' . $r->input('texto') . '%')
                ->orderBy('modelo_nom')
                ->get()
        );
    }

    public function buscarModelosItem(Request $r)
    {
        return response()->json(
            Modelo::select('id', 'modelo_nom')
                ->where('marca_id', $r->input('marca_id'))
                ->where('modelo_nom', 'ILIKE', '%' . $r->input('texto', '') . '%')
                ->orderBy('modelo_nom')
                ->get()
        );
    }
}
