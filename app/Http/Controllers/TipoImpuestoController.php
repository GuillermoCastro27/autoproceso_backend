<?php

namespace App\Http\Controllers;

use App\Models\TipoImpuesto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoImpuestoController extends Controller
{
    public function read()
    {
        return TipoImpuesto::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'tip_imp_nom'   => 'required|string|max:100|unique:tipo_impuesto,tip_imp_nom',
            'tipo_imp_tasa' => 'required|numeric|min:0|max:100',
        ], [
            'tip_imp_nom.required'   => 'El nombre del impuesto es obligatorio.',
            'tip_imp_nom.unique'     => 'Ya existe un tipo de impuesto con ese nombre.',
            'tipo_imp_tasa.required' => 'La tasa del impuesto es obligatoria.',
            'tipo_imp_tasa.numeric'  => 'La tasa debe ser un valor numérico.',
            'tipo_imp_tasa.min'      => 'La tasa no puede ser negativa.',
            'tipo_imp_tasa.max'      => 'La tasa no puede superar el 100%.',
        ]);

        $tipoimpuesto = TipoImpuesto::create([
            'tip_imp_nom'   => $r->tip_imp_nom,
            'tipo_imp_tasa' => $r->tipo_imp_tasa,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de impuesto creado con éxito',
            'tipo'     => 'success',
            'registro' => $tipoimpuesto,
        ]);
    }

    public function update(Request $r, $id)
    {
        $tipoimpuesto = TipoImpuesto::find($id);
        if (!$tipoimpuesto) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tip_imp_nom'   => ['required', 'string', 'max:100', Rule::unique('tipo_impuesto', 'tip_imp_nom')->ignore($id)],
            'tipo_imp_tasa' => 'required|numeric|min:0|max:100',
        ], [
            'tip_imp_nom.required'   => 'El nombre del impuesto es obligatorio.',
            'tip_imp_nom.unique'     => 'Ya existe otro tipo de impuesto con ese nombre.',
            'tipo_imp_tasa.required' => 'La tasa del impuesto es obligatoria.',
            'tipo_imp_tasa.numeric'  => 'La tasa debe ser un valor numérico.',
            'tipo_imp_tasa.min'      => 'La tasa no puede ser negativa.',
            'tipo_imp_tasa.max'      => 'La tasa no puede superar el 100%.',
        ]);

        $tipoimpuesto->update([
            'tip_imp_nom'   => $r->tip_imp_nom,
            'tipo_imp_tasa' => $r->tipo_imp_tasa,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de impuesto actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $tipoimpuesto,
        ]);
    }

    public function destroy($id)
    {
        $tipoimpuesto = TipoImpuesto::find($id);
        if (!$tipoimpuesto) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $tipoimpuesto->delete();
            return response()->json(['mensaje' => 'Tipo de impuesto eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el tipo de impuesto porque está siendo utilizado en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }

    public function buscar(Request $r)
    {
        $query = $r->input('query', '');
        return TipoImpuesto::where('tip_imp_nom', 'ilike', "%{$query}%")
            ->orWhere('tipo_imp_tasa', 'ilike', "%{$query}%")
            ->get();
    }
}
