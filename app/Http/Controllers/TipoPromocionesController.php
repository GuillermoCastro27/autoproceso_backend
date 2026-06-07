<?php

namespace App\Http\Controllers;

use App\Models\TipoPromociones;
use Illuminate\Http\Request;

class TipoPromocionesController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoPromociones::select(
                'id as tipo_promociones_id',
                'tipo_prom_nombre',
                'tipo_prom_descrip',
                'tipo_prom_fechaInicio',
                'tipo_prom_fechaFin',
                'tipo_prom_modo',
                'tipo_prom_valor'
            )->get()
        );
    }

    public function store(Request $r)
    {
        // ✅ ÚNICA VALIDACIÓN
        $datosValidados = $r->validate([
            'tipo_prom_nombre'       => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_promociones')
                        ->whereRaw('LOWER(tipo_prom_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de promoción con ese nombre.');
                    }
                },
            ],
            'tipo_prom_descrip'      => 'required|string|max:255|not_regex:/[*<>{}|]/',
            'tipo_prom_fechaInicio'  => 'required|date_format:d/m/Y H:i:s',
            'tipo_prom_fechaFin'     => 'required|date_format:d/m/Y H:i:s|after_or_equal:tipo_prom_fechaInicio',
            'tipo_prom_modo'         => 'required|string|max:50',
            'tipo_prom_valor'        => 'required|numeric|min:0',
        ], [
            'tipo_prom_nombre.required'               => 'El nombre de la promoción es obligatorio.',
            'tipo_prom_nombre.not_regex'              => 'El nombre contiene caracteres no permitidos.',
            'tipo_prom_descrip.required'              => 'La descripción es obligatoria.',
            'tipo_prom_descrip.not_regex'             => 'La descripción contiene caracteres no permitidos.',
            'tipo_prom_fechaInicio.required'          => 'Debe indicar la fecha de inicio.',
            'tipo_prom_fechaInicio.date_format'       => 'El formato de la fecha de inicio no es válido.',
            'tipo_prom_fechaFin.required'             => 'Debe indicar la fecha de finalización.',
            'tipo_prom_fechaFin.date_format'          => 'El formato de la fecha de fin no es válido.',
            'tipo_prom_fechaFin.after_or_equal'       => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'tipo_prom_modo.required'                 => 'Debe seleccionar el modo de promoción.',
            'tipo_prom_valor.required'                => 'Debe ingresar un valor.',
            'tipo_prom_valor.numeric'                 => 'El valor debe ser un número.',
            'tipo_prom_valor.min'                     => 'El valor no puede ser negativo.',
        ]);

        $tipopromociones = TipoPromociones::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $tipopromociones
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $tipopromociones = TipoPromociones::find($id);
        if (!$tipopromociones) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // ✅ ÚNICA VALIDACIÓN
        $datosValidados = $r->validate([
            'tipo_prom_nombre'       => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_promociones')
                        ->whereRaw('LOWER(tipo_prom_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de promoción con ese nombre.');
                    }
                },
            ],
            'tipo_prom_descrip'      => 'required|string|max:255|not_regex:/[*<>{}|]/',
            'tipo_prom_fechaInicio'  => 'required|date_format:d/m/Y H:i:s',
            'tipo_prom_fechaFin'     => 'required|date_format:d/m/Y H:i:s|after_or_equal:tipo_prom_fechaInicio',
            'tipo_prom_modo'         => 'required|string|max:50',
            'tipo_prom_valor'        => 'required|numeric|min:0',
        ], [
            'tipo_prom_nombre.required'               => 'El nombre de la promoción es obligatorio.',
            'tipo_prom_nombre.not_regex'              => 'El nombre contiene caracteres no permitidos.',
            'tipo_prom_descrip.required'              => 'La descripción es obligatoria.',
            'tipo_prom_descrip.not_regex'             => 'La descripción contiene caracteres no permitidos.',
            'tipo_prom_fechaInicio.required'          => 'Debe indicar la fecha de inicio.',
            'tipo_prom_fechaInicio.date_format'       => 'El formato de la fecha de inicio no es válido.',
            'tipo_prom_fechaFin.required'             => 'Debe indicar la fecha de finalización.',
            'tipo_prom_fechaFin.date_format'          => 'El formato de la fecha de fin no es válido.',
            'tipo_prom_fechaFin.after_or_equal'       => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'tipo_prom_modo.required'                 => 'Debe seleccionar el modo de promoción.',
            'tipo_prom_valor.required'                => 'Debe ingresar un valor.',
            'tipo_prom_valor.numeric'                 => 'El valor debe ser un número.',
            'tipo_prom_valor.min'                     => 'El valor no puede ser negativo.',
        ]);

        $tipopromociones->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $tipopromociones
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $tipopromociones = TipoPromociones::find($id);
        if (!$tipopromociones) {
            return response()->json(['mensaje' => 'Tipo de Promoción no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($tipopromociones->tipo_prom_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $tipopromociones->update(['tipo_prom_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo de Promoción activado con éxito.' : 'Tipo de Promoción desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
