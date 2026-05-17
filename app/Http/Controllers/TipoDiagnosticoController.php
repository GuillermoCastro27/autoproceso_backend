<?php

namespace App\Http\Controllers;

use App\Models\TipoDiagnostico;
use Illuminate\Http\Request;

class TipoDiagnosticoController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoDiagnostico::select(
                'id as tipo_diagnostico_id',
                'tipo_diag_nombre',
                'tipo_diag_descrip'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'tipo_diag_nombre' => 'required|string|max:100|unique:tipo_diagnostico,tipo_diag_nombre',
            'tipo_diag_descrip' => 'required|string|max:255',
        ], [
            'tipo_diag_nombre.required' => 'El nombre del tipo de diagnóstico es obligatorio.',
            'tipo_diag_nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'tipo_diag_nombre.unique'   => 'Ya existe un tipo de diagnóstico con ese nombre.',
            'tipo_diag_descrip.required'=> 'La descripción es obligatoria.',
            'tipo_diag_descrip.max'     => 'La descripción no puede superar los 255 caracteres.',
        ]);

        $registro = TipoDiagnostico::create([
            'tipo_diag_nombre' => $r->tipo_diag_nombre,
            'tipo_diag_descrip' => $r->tipo_diag_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de diagnóstico creado con éxito',
            'tipo'     => 'success',
            'registro' => $registro
        ]);
    }

    public function update(Request $r, $id)
    {
        $registro = TipoDiagnostico::find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tipo_diag_nombre' => 'required|string|max:100|unique:tipo_diagnostico,tipo_diag_nombre,' . $id,
            'tipo_diag_descrip' => 'required|string|max:255',
        ], [
            'tipo_diag_nombre.required' => 'El nombre del tipo de diagnóstico es obligatorio.',
            'tipo_diag_nombre.max'      => 'El nombre no puede superar los 100 caracteres.',
            'tipo_diag_nombre.unique'   => 'Ya existe otro tipo de diagnóstico con ese nombre.',
            'tipo_diag_descrip.required'=> 'La descripción es obligatoria.',
            'tipo_diag_descrip.max'     => 'La descripción no puede superar los 255 caracteres.',
        ]);

        $registro->update([
            'tipo_diag_nombre' => $r->tipo_diag_nombre,
            'tipo_diag_descrip' => $r->tipo_diag_descrip,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de diagnóstico actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $registro
        ]);
    }

    public function destroy($id)
    {
        $registro = TipoDiagnostico::find($id);

        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $registro->delete();
            return response()->json(['mensaje' => 'Tipo de diagnóstico eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar porque está siendo utilizado en el sistema.',
                'tipo'    => 'error'
            ], 409);
        }
    }
}
