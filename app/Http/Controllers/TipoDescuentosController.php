<?php

namespace App\Http\Controllers;

use App\Models\TipoDescuentos;
use Illuminate\Http\Request;

class TipoDescuentosController extends Controller
{
    // 📋 Listar todos
    public function read()
    {
        return response()->json(
            TipoDescuentos::select(
                'id as tipo_descuentos_id',
                'tipo_desc_nombre',
                'tipo_desc_descrip',
                'tipo_desc_fechaInicio',
                'tipo_desc_fechaFin'
            )->get()
        );
    }

    // 🆕 Crear tipo de descuento
    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'tipo_desc_nombre'      => 'required|string|max:100|unique:tipo_descuentos,tipo_desc_nombre',
            'tipo_desc_descrip'     => 'required|string|max:255',
            'tipo_desc_fechaInicio' => 'required|date',
            'tipo_desc_fechaFin'    => 'required|date'
        ], [
            'tipo_desc_nombre.required'  => 'El nombre del descuento es obligatorio.',
            'tipo_desc_nombre.unique'    => 'Ya existe un tipo de descuento con ese nombre.',
            'tipo_desc_descrip.required' => 'La descripción es obligatoria.',
            'tipo_desc_fechaInicio.required' => 'Debe indicar la fecha de inicio.',
            'tipo_desc_fechaFin.required'    => 'Debe indicar la fecha de finalización.'
        ]);

        $tipodescuentos = TipoDescuentos::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $tipodescuentos
        ], 200);
    }

    // ✏️ Actualizar tipo de descuento
    public function update(Request $r, $id)
    {
        $tipodescuentos = TipoDescuentos::find($id);
        if (!$tipodescuentos) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'tipo_desc_nombre'      => 'required|string|max:100|unique:tipo_descuentos,tipo_desc_nombre,' . $id,
            'tipo_desc_descrip'     => 'required|string|max:255',
            'tipo_desc_fechaInicio' => 'required|date',
            'tipo_desc_fechaFin'    => 'required|date'
        ], [
            'tipo_desc_nombre.required'  => 'El nombre del descuento es obligatorio.',
            'tipo_desc_nombre.unique'    => 'Ya existe un tipo de descuento con ese nombre.',
            'tipo_desc_descrip.required' => 'La descripción es obligatoria.',
            'tipo_desc_fechaInicio.required' => 'Debe indicar la fecha de inicio.',
            'tipo_desc_fechaFin.required'    => 'Debe indicar la fecha de finalización.'
        ]);

        $tipodescuentos->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $tipodescuentos
        ], 200);
    }

    // 🗑️ Eliminar tipo de descuento
    public function destroy($id)
    {
        $tipodescuentos = TipoDescuentos::find($id);
        if (!$tipodescuentos) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        try {
            $tipodescuentos->delete();
            return response()->json(['mensaje' => 'Tipo de descuento eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el tipo de descuento porque está siendo utilizado en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
