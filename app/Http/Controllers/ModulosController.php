<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModulosController extends Controller
{
    public function read()
    {
        return Modulo::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'mod_nombre'      => ['required', 'string', 'max:100', 'unique:modulos,mod_nombre', 'not_regex:/[*<>{}|]/'],
            'mod_descripcion' => ['required', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'mod_estado'      => 'required|string|max:20',
        ], [
            'mod_nombre.required'       => 'El nombre del módulo es obligatorio.',
            'mod_nombre.unique'         => 'Ya existe un módulo con ese nombre.',
            'mod_nombre.not_regex'      => 'El nombre del módulo contiene caracteres no permitidos.',
            'mod_descripcion.required'  => 'La descripción es obligatoria.',
            'mod_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
            'mod_estado.required'       => 'El estado es obligatorio.',
        ]);

        $modulo = Modulo::create([
            'mod_nombre'      => $r->mod_nombre,
            'mod_descripcion' => $r->mod_descripcion,
            'mod_estado'      => $r->mod_estado,
        ]);

        return response()->json([
            'mensaje'  => 'Módulo creado con éxito',
            'tipo'     => 'success',
            'registro' => $modulo,
        ]);
    }

    public function update(Request $r, $id)
    {
        $modulo = Modulo::find($id);
        if (!$modulo) {
            return response()->json(['mensaje' => 'Módulo no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'mod_nombre'      => ['required', 'string', 'max:100', Rule::unique('modulos', 'mod_nombre')->ignore($id), 'not_regex:/[*<>{}|]/'],
            'mod_descripcion' => ['required', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'mod_estado'      => 'required|string|max:20',
        ], [
            'mod_nombre.required'       => 'El nombre del módulo es obligatorio.',
            'mod_nombre.unique'         => 'Ya existe otro módulo con ese nombre.',
            'mod_nombre.not_regex'      => 'El nombre del módulo contiene caracteres no permitidos.',
            'mod_descripcion.required'  => 'La descripción es obligatoria.',
            'mod_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
            'mod_estado.required'       => 'El estado es obligatorio.',
        ]);

        $modulo->update([
            'mod_nombre'      => $r->mod_nombre,
            'mod_descripcion' => $r->mod_descripcion,
            'mod_estado'      => $r->mod_estado,
        ]);

        return response()->json([
            'mensaje'  => 'Módulo actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $modulo,
        ]);
    }

    public function destroy($id)
    {
        $modulo = Modulo::find($id);
        if (!$modulo) {
            return response()->json(['mensaje' => 'Módulo no encontrado', 'tipo' => 'error'], 404);
        }

        $tieneAccesos = DB::table('accesos')->where('mod_id', $id)->exists();
        if ($tieneAccesos) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el módulo porque tiene accesos configurados.',
                'tipo'    => 'error',
            ], 409);
        }

        $tienePermisos = DB::table('permisos')->whereRaw("per_nombre LIKE ?", [$modulo->mod_nombre . '.%'])->exists();
        if ($tienePermisos) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el módulo porque tiene permisos asociados.',
                'tipo'    => 'error',
            ], 409);
        }

        try {
            $modulo->delete();
            return response()->json(['mensaje' => 'Módulo eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el módulo porque tiene registros asociados.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
