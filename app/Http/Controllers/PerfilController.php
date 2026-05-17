<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function read()
    {
        return Perfil::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'pref_descripcion' => 'required|string|max:100|unique:perfiles,pref_descripcion',
        ], [
            'pref_descripcion.required' => 'El nombre del perfil es obligatorio.',
            'pref_descripcion.max'      => 'El nombre no puede superar los 100 caracteres.',
            'pref_descripcion.unique'   => 'Ya existe un perfil con ese nombre.',
        ]);

        $perfil = Perfil::create([
            'pref_descripcion'  => $r->pref_descripcion,
            'pref_superadmin'   => $r->boolean('pref_superadmin', false),
        ]);

        return response()->json([
            'mensaje'  => 'Perfil creado con éxito',
            'tipo'     => 'success',
            'registro' => $perfil
        ]);
    }

    public function update(Request $r, $id)
    {
        $perfil = Perfil::find($id);

        if (!$perfil) {
            return response()->json(['mensaje' => 'Perfil no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'pref_descripcion' => [
                'required', 'string', 'max:100',
                Rule::unique('perfiles', 'pref_descripcion')->ignore($id),
            ],
        ], [
            'pref_descripcion.required' => 'El nombre del perfil es obligatorio.',
            'pref_descripcion.max'      => 'El nombre no puede superar los 100 caracteres.',
            'pref_descripcion.unique'   => 'Ya existe otro perfil con ese nombre.',
        ]);

        $perfil->update([
            'pref_descripcion' => $r->pref_descripcion,
            'pref_superadmin'  => $r->boolean('pref_superadmin', $perfil->pref_superadmin),
        ]);

        return response()->json([
            'mensaje'  => 'Perfil actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $perfil
        ]);
    }

    public function destroy($id)
    {
        $perfil = Perfil::find($id);

        if (!$perfil) {
            return response()->json(['mensaje' => 'Perfil no encontrado', 'tipo' => 'error'], 404);
        }

        $enUso = \DB::table('users')->where('perfil_id', $id)->exists();
        if ($enUso) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el perfil porque tiene usuarios asignados.',
                'tipo'    => 'error'
            ], 409);
        }

        $tieneAccesos = \DB::table('accesos')->where('perfil_id', $id)->exists();
        if ($tieneAccesos) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el perfil porque tiene accesos configurados. Elimine los accesos primero.',
                'tipo'    => 'error'
            ], 409);
        }

        $perfil->delete();

        return response()->json(['mensaje' => 'Perfil eliminado con éxito', 'tipo' => 'success']);
    }

    public function buscar(Request $request)
    {
        return Perfil::where('pref_descripcion', 'ILIKE', '%' . $request->q . '%')
            ->orderBy('pref_descripcion')
            ->get();
    }
}
