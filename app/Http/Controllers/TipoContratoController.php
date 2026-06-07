<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoContrato;

class TipoContratoController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoContrato::select(
            'id as tipo_contrato_id',
            'tip_con_nombre as tip_con_nombre',
            'tip_con_objeto as tip_con_objeto',
            'tip_con_alcance as tip_con_alcance',
            'tip_con_garantia as tip_con_garantia',
            'tip_con_responsabilidad as tip_con_responsabilidad',
            'tip_con_limitacion as tip_con_limitacion',
            'tip_con_fuerza_mayor as tip_con_fuerza_mayor',
            'tip_con_jurisdiccion as tip_con_jurisdiccion',
            'tip_con_estado as tip_con_estado'
            )->get()
        );
    }
    public function store(Request $r)
{
    $datosValidados = $r->validate([
        'tip_con_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_contrato')
                        ->whereRaw('LOWER(tip_con_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('El tipo de contrato ya existe.');
                    }
                },
            ],

        'tip_con_objeto'          => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_alcance'         => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_garantia'        => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_responsabilidad' => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_limitacion'      => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_fuerza_mayor'    => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_jurisdiccion'    => 'required|string|max:2000|not_regex:/[*<>{}|]/',

        'tip_con_estado' => 'nullable|string|max:20'
    ], [
        'tip_con_nombre.required'  => 'El nombre del tipo de contrato es obligatorio.',
        'tip_con_nombre.unique'    => 'El tipo de contrato ya existe.',
        'tip_con_nombre.not_regex' => 'El nombre contiene caracteres no permitidos.',

        'tip_con_objeto.required' => 'El objeto del contrato es obligatorio.',
        'tip_con_alcance.required' => 'El alcance del contrato es obligatorio.',
        'tip_con_garantia.required' => 'La garantía es obligatoria.',
        'tip_con_responsabilidad.required' => 'La responsabilidad es obligatoria.',
        'tip_con_limitacion.required' => 'La limitación es obligatoria.',
        'tip_con_fuerza_mayor.required' => 'La cláusula de fuerza mayor es obligatoria.',
        'tip_con_jurisdiccion.required' => 'La jurisdicción es obligatoria.',
    ]);

    // Estado por defecto si no viene
    if (!isset($datosValidados['tip_con_estado'])) {
        $datosValidados['tip_con_estado'] = 'activo';
    }

    $tipoContrato = TipoContrato::create($datosValidados);

    return response()->json([
        'mensaje' => 'Tipo de contrato creado con éxito',
        'tipo' => 'success',
        'registro' => $tipoContrato
    ], 200);
}
public function update(Request $r, $id)
{
    $tipoContrato = TipoContrato::find($id);

    if (!$tipoContrato) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    $datosValidados = $r->validate([
        'tip_con_nombre' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_contrato')
                        ->whereRaw('LOWER(tip_con_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('El tipo de contrato ya existe.');
                    }
                },
            ],

        'tip_con_objeto'          => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_alcance'         => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_garantia'        => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_responsabilidad' => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_limitacion'      => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_fuerza_mayor'    => 'required|string|max:2000|not_regex:/[*<>{}|]/',
        'tip_con_jurisdiccion'    => 'required|string|max:2000|not_regex:/[*<>{}|]/',

        'tip_con_estado' => 'nullable|string|max:20'
    ], [
        'tip_con_nombre.required'  => 'El nombre del tipo de contrato es obligatorio.',
        'tip_con_nombre.unique'    => 'El tipo de contrato ya existe.',
        'tip_con_nombre.not_regex' => 'El nombre contiene caracteres no permitidos.',

        'tip_con_objeto.required' => 'El objeto del contrato es obligatorio.',
        'tip_con_alcance.required' => 'El alcance del contrato es obligatorio.',
        'tip_con_garantia.required' => 'La garantía es obligatoria.',
        'tip_con_responsabilidad.required' => 'La responsabilidad es obligatoria.',
        'tip_con_limitacion.required' => 'La limitación es obligatoria.',
        'tip_con_fuerza_mayor.required' => 'La cláusula de fuerza mayor es obligatoria.',
        'tip_con_jurisdiccion.required' => 'La jurisdicción es obligatoria.',
    ]);

    $tipoContrato->update($datosValidados);

    return response()->json([
        'mensaje' => 'Tipo de contrato modificado con éxito',
        'tipo' => 'success',
        'registro' => $tipoContrato
    ], 200);
}
public function cambiarEstado($id)
{
    $tipoContrato = TipoContrato::find($id);
    if (!$tipoContrato) {
        return response()->json(['mensaje' => 'Tipo de Contrato no encontrado', 'tipo' => 'error'], 404);
    }
    $nuevoEstado = strtolower($tipoContrato->tip_con_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
    $tipoContrato->update(['tip_con_estado' => $nuevoEstado]);
    $msg = $nuevoEstado === 'activo' ? 'Tipo de Contrato activado con éxito.' : 'Tipo de Contrato desactivado con éxito.';
    return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
}
}
