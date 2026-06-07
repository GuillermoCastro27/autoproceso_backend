<?php

namespace App\Http\Controllers;

use App\Models\Nacionalidad;
use Illuminate\Http\Request;

class NacionalidadController extends Controller
{
    public function read()
    {
        return \DB::select(
            'SELECT n.id, n.nacio_descripcion, n.pais_id, p.pais_descrpcion
             FROM nacionalidad n
             LEFT JOIN paises p ON p.id = n.pais_id
             ORDER BY n.nacio_descripcion'
        );
    }

    public function readPorPais($pais_id)
    {
        $nac = \DB::table('nacionalidad')
            ->where('pais_id', $pais_id)
            ->first();

        if (!$nac) {
            return response()->json(['mensaje' => 'No hay nacionalidad registrada para ese país.'], 404);
        }

        return response()->json($nac);
    }

    public function store(Request $r)
    {
        $r->validate([
            'nacio_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('nacionalidad')
                        ->whereRaw('LOWER(nacio_descripcion) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una nacionalidad con esa descripción.');
                    }
                },
            ],
        ], [
            'nacio_descripcion.required'  => 'La descripción de la nacionalidad es obligatoria.',
            'nacio_descripcion.max'       => 'La descripción no puede superar los 100 caracteres.',
            'nacio_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        $r->validate([
            'pais_id' => 'nullable|integer|exists:paises,id',
        ]);

        $nacionalidad = Nacionalidad::create([
            'nacio_descripcion' => $r->nacio_descripcion,
            'pais_id'           => $r->pais_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Nacionalidad creada con éxito',
            'tipo'     => 'success',
            'registro' => $nacionalidad,
        ]);
    }

    public function update(Request $r, $id)
    {
        $nacionalidad = Nacionalidad::find($id);
        if (!$nacionalidad) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'nacio_descripcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('nacionalidad')
                        ->whereRaw('LOWER(nacio_descripcion) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra nacionalidad con esa descripción.');
                    }
                },
            ],
        ], [
            'nacio_descripcion.required'  => 'La descripción de la nacionalidad es obligatoria.',
            'nacio_descripcion.max'       => 'La descripción no puede superar los 100 caracteres.',
            'nacio_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        $nacionalidad->update([
            'nacio_descripcion' => $r->nacio_descripcion,
            'pais_id'           => $r->pais_id ?: null,
        ]);

        return response()->json([
            'mensaje'  => 'Nacionalidad actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $nacionalidad,
        ]);
    }

    public function destroy($id)
    {
        $nacionalidad = Nacionalidad::find($id);
        if (!$nacionalidad) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $nacionalidad->delete();
            return response()->json(['mensaje' => 'Nacionalidad eliminada con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la nacionalidad porque está siendo utilizada en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
