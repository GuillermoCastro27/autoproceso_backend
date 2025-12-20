<?php

namespace App\Http\Controllers;

use App\Models\FormaCobro;
use Illuminate\Http\Request;

class FormaCobroController extends Controller
{
    public function read()
    {
        return response()->json(
            FormaCobro::select(
                'id as forma_cobro_id',
                'for_cob_descripcion'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $datos = $r->validate([
            'for_cob_descripcion' => 'required|string|max:100|unique:forma_cobro,for_cob_descripcion'
        ], [
            'for_cob_descripcion.required' => 'La descripción es obligatoria.',
            'for_cob_descripcion.unique'   => 'La forma de cobro ya existe.'
        ]);

        $forma = FormaCobro::create($datos);

        return response()->json([
            'mensaje'  => 'Forma de cobro registrada con éxito',
            'tipo'     => 'success',
            'registro' => $forma
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $forma = FormaCobro::find($id);

        if (!$forma) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datos = $r->validate([
            'for_cob_descripcion' => 'required|string|max:100|unique:forma_cobro,for_cob_descripcion,' . $id
        ]);

        $forma->update($datos);

        return response()->json([
            'mensaje'  => 'Forma de cobro modificada con éxito',
            'tipo'     => 'success',
            'registro' => $forma
        ], 200);
    }

    public function destroy($id)
    {
        $forma = FormaCobro::find($id);

        if (!$forma) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $forma->delete();

        return response()->json([
            'mensaje' => 'Forma de cobro eliminada con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
