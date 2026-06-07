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
        $r->validate([
            'for_cob_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('forma_cobro')
                        ->whereRaw('LOWER(for_cob_descripcion) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una forma de cobro con esa descripción.');
                    }
                },
            ],
        ], [
            'for_cob_descripcion.required' => 'La descripción es obligatoria.',
        ]);
        $datos = $r->only(['for_cob_descripcion']);

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

        $r->validate([
            'for_cob_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('forma_cobro')
                        ->whereRaw('LOWER(for_cob_descripcion) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra forma de cobro con esa descripción.');
                    }
                },
            ],
        ]);
        $datos = $r->only(['for_cob_descripcion']);

        $forma->update($datos);

        return response()->json([
            'mensaje'  => 'Forma de cobro modificada con éxito',
            'tipo'     => 'success',
            'registro' => $forma
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $forma = FormaCobro::find($id);
        if (!$forma) {
            return response()->json(['mensaje' => 'Forma de Cobro no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($forma->for_cob_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $forma->update(['for_cob_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Forma de Cobro activada con éxito.' : 'Forma de Cobro desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
