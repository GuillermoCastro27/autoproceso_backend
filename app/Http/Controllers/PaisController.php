<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use App\Models\Nacionalidad;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function read()
    {
        return \DB::select(
            'SELECT p.id, p.pais_descrpcion, p.pais_siglas,
                    n.id AS nacio_id, n.nacio_descripcion
             FROM paises p
             LEFT JOIN nacionalidad n ON n.pais_id = p.id
             ORDER BY p.pais_descrpcion'
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'pais_descrpcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('paises')
                        ->whereRaw('LOWER(pais_descrpcion) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un país con ese nombre.');
                    }
                },
            ],
            'pais_siglas' => [
                'required', 'string', 'max:10',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('paises')
                        ->whereRaw('LOWER(pais_siglas) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un país con esas siglas.');
                    }
                },
            ],
        ], [
            'pais_descrpcion.required'  => 'El nombre del país es obligatorio.',
            'pais_descrpcion.not_regex' => 'El nombre del país contiene caracteres no permitidos.',
            'pais_siglas.required'      => 'Las siglas son obligatorias.',
        ]);

        $pais = Pais::create([
            'pais_descrpcion' => $r->pais_descrpcion,
            'pais_siglas'     => $r->pais_siglas,
        ]);

        if ($r->filled('nacio_descripcion')) {
            Nacionalidad::create([
                'nacio_descripcion' => $r->nacio_descripcion,
                'pais_id'           => $pais->id,
            ]);
        }

        return response()->json([
            'mensaje'  => 'País creado con éxito',
            'tipo'     => 'success',
            'registro' => $pais,
        ]);
    }

    public function update(Request $r, $id)
    {
        $pais = Pais::find($id);
        if (!$pais) {
            return response()->json(['mensaje' => 'País no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'pais_descrpcion' => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('paises')
                        ->whereRaw('LOWER(pais_descrpcion) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro país con ese nombre.');
                    }
                },
            ],
            'pais_siglas' => [
                'required', 'string', 'max:10',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('paises')
                        ->whereRaw('LOWER(pais_siglas) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro país con esas siglas.');
                    }
                },
            ],
        ], [
            'pais_descrpcion.required'  => 'El nombre del país es obligatorio.',
            'pais_descrpcion.not_regex' => 'El nombre del país contiene caracteres no permitidos.',
            'pais_siglas.required'      => 'Las siglas son obligatorias.',
        ]);

        $pais->update([
            'pais_descrpcion' => $r->pais_descrpcion,
            'pais_siglas'     => $r->pais_siglas,
        ]);

        if ($r->filled('nacio_descripcion')) {
            Nacionalidad::updateOrCreate(
                ['pais_id' => $pais->id],
                ['nacio_descripcion' => $r->nacio_descripcion]
            );
        }

        return response()->json([
            'mensaje'  => 'País actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $pais,
        ]);
    }

    public function destroy($id)
    {
        $pais = Pais::find($id);
        if (!$pais) {
            return response()->json(['mensaje' => 'País no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $pais->delete();
            return response()->json(['mensaje' => 'País eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el país porque tiene ciudades u otros registros asociados.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
