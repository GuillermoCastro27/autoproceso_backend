<?php

namespace App\Http\Controllers;

use App\Models\TipoImpuesto;
use Illuminate\Http\Request;

class TipoImpuestoController extends Controller
{
    public function read()
    {
        return TipoImpuesto::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'tip_imp_nom'   => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_impuesto')
                        ->whereRaw('LOWER(tip_imp_nom) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de impuesto con ese nombre.');
                    }
                },
            ],
            'tipo_imp_tasa' => 'required|numeric|min:0|max:100',
        ], [
            'tip_imp_nom.required'   => 'El nombre del impuesto es obligatorio.',
            'tip_imp_nom.not_regex'  => 'El nombre contiene caracteres no permitidos.',
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
            'tip_imp_nom'   => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_impuesto')
                        ->whereRaw('LOWER(tip_imp_nom) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro tipo de impuesto con ese nombre.');
                    }
                },
            ],
            'tipo_imp_tasa' => 'required|numeric|min:0|max:100',
        ], [
            'tip_imp_nom.required'   => 'El nombre del impuesto es obligatorio.',
            'tip_imp_nom.not_regex'  => 'El nombre contiene caracteres no permitidos.',
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

    public function cambiarEstado($id)
    {
        $tipoimpuesto = TipoImpuesto::find($id);
        if (!$tipoimpuesto) {
            return response()->json(['mensaje' => 'Tipo de Impuesto no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($tipoimpuesto->tip_imp_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $tipoimpuesto->update(['tip_imp_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo de Impuesto activado con éxito.' : 'Tipo de Impuesto desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }

    public function buscar(Request $r)
    {
        $query = $r->input('query', '');
        return TipoImpuesto::where('tip_imp_nom', 'ilike', "%{$query}%")
            ->orWhere('tipo_imp_tasa', 'ilike', "%{$query}%")
            ->get();
    }
}
