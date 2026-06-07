<?php

namespace App\Http\Controllers;

use App\Models\TipoComprobante;
use Illuminate\Http\Request;

class TipoComprobanteController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoComprobante::select('id', 'tip_comp_nombre', 'tip_comp_abrev')->get()
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'tip_comp_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($a, $v, $fail) {
                    if (\DB::table('tipo_comprobante')->whereRaw('LOWER(tip_comp_nombre) = LOWER(?)', [trim($v)])->exists()) {
                        $fail('Ya existe un tipo de comprobante con ese nombre.');
                    }
                },
            ],
            'tip_comp_abrev' => ['required', 'string', 'max:10', 'not_regex:/[*<>{}|]/'],
        ], [
            'tip_comp_nombre.required' => 'El nombre del tipo de comprobante es obligatorio.',
            'tip_comp_abrev.required'  => 'La abreviatura es obligatoria.',
        ]);

        $registro = TipoComprobante::create($r->only(['tip_comp_nombre', 'tip_comp_abrev']));

        return response()->json(['mensaje' => 'Tipo de comprobante registrado con éxito', 'tipo' => 'success', 'registro' => $registro]);
    }

    public function update(Request $r, $id)
    {
        $registro = TipoComprobante::find($id);
        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tip_comp_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($a, $v, $fail) use ($id) {
                    if (\DB::table('tipo_comprobante')->whereRaw('LOWER(tip_comp_nombre) = LOWER(?)', [trim($v)])->where('id', '!=', $id)->exists()) {
                        $fail('Ya existe otro tipo de comprobante con ese nombre.');
                    }
                },
            ],
            'tip_comp_abrev' => ['required', 'string', 'max:10', 'not_regex:/[*<>{}|]/'],
        ], [
            'tip_comp_nombre.required' => 'El nombre del tipo de comprobante es obligatorio.',
            'tip_comp_abrev.required'  => 'La abreviatura es obligatoria.',
        ]);

        $registro->update($r->only(['tip_comp_nombre', 'tip_comp_abrev']));

        return response()->json(['mensaje' => 'Tipo de comprobante modificado con éxito', 'tipo' => 'success', 'registro' => $registro]);
    }

    public function destroy($id)
    {
        $registro = TipoComprobante::find($id);
        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $registro->delete();
            return response()->json(['mensaje' => 'Tipo de comprobante eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'No se puede eliminar porque está siendo utilizado en el sistema.', 'tipo' => 'error'], 409);
        }
    }
}
