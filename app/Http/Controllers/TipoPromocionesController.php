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
            'tipo_prom_nombre'       => 'required|string|max:100|unique:tipo_promociones,tipo_prom_nombre',
            'tipo_prom_descrip'      => 'required|string|max:255',
            'tipo_prom_fechaInicio'  => 'required',
            'tipo_prom_fechaFin'     => 'required',
            'tipo_prom_modo'         => 'required',
            'tipo_prom_valor'        => 'required'
        ], [
            'tipo_prom_nombre.required' => 'El nombre de la promoción es obligatorio.',
            'tipo_prom_nombre.unique'   => 'Ya existe un tipo de promoción con ese nombre.',
            'tipo_prom_descrip.required' => 'La descripción es obligatoria.',
            'tipo_prom_fechaInicio.required' => 'Debe indicar la fecha de inicio.',
            'tipo_prom_fechaFin.required'    => 'Debe indicar la fecha de finalización.',
            'tipo_prom_modo.required'        => 'Debe seleccionar el modo de promoción.',
            'tipo_prom_valor.required'       => 'Debe ingresar un valor.'
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
            'tipo_prom_nombre'       => 'required|string|max:100|unique:tipo_promociones,tipo_prom_nombre,' . $id,
            'tipo_prom_descrip'      => 'required|string|max:255',
            'tipo_prom_fechaInicio'  => 'required',
            'tipo_prom_fechaFin'     => 'required',
            'tipo_prom_modo'         => 'required',
            'tipo_prom_valor'        => 'required'
        ], [
            'tipo_prom_nombre.required' => 'El nombre de la promoción es obligatorio.',
            'tipo_prom_nombre.unique'   => 'Ya existe un tipo de promoción con ese nombre.',
            'tipo_prom_descrip.required' => 'La descripción es obligatoria.',
            'tipo_prom_fechaInicio.required' => 'Debe indicar la fecha de inicio.',
            'tipo_prom_fechaFin.required'    => 'Debe indicar la fecha de finalización.',
            'tipo_prom_modo.required'        => 'Debe seleccionar el modo de promoción.',
            'tipo_prom_valor.required'       => 'Debe ingresar un valor.'
        ]);

        $tipopromociones->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $tipopromociones
        ], 200);
    }

    public function destroy($id)
    {
        $tipopromociones = TipoPromociones::find($id);
        if (!$tipopromociones) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $tipopromociones->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success',
        ], 200);
    }
}
