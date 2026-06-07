<?php

namespace App\Http\Controllers;

use App\Models\EquipoTrabajo;
use Illuminate\Http\Request;

class EquipoTrabajoController extends Controller
{
    public function read()
    {
        return response()->json(
            EquipoTrabajo::select(
                'id as equipo_trabajo_id',
                'equipo_nombre',
                'equipo_descripcion',
                'equipo_categoria'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'equipo_nombre'      => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('equipo_trabajo')
                        ->whereRaw('LOWER(equipo_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un equipo con ese nombre.');
                    }
                },
            ],
            'equipo_descripcion' => 'nullable|string|max:255|not_regex:/[*<>{}|]/',
            'equipo_categoria'   => 'nullable|string|max:50|not_regex:/[*<>{}|]/',
        ], [
            'equipo_nombre.required'       => 'El campo nombre es obligatorio.',
            'equipo_nombre.not_regex'      => 'El nombre contiene caracteres no permitidos.',
            'equipo_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        $equipo = EquipoTrabajo::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $equipo,
        ]);
    }

    public function update(Request $r, $id)
    {
        $equipo = EquipoTrabajo::find($id);
        if (!$equipo) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'equipo_nombre'      => [
                'required', 'string', 'max:100', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('equipo_trabajo')
                        ->whereRaw('LOWER(equipo_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro equipo con ese nombre.');
                    }
                },
            ],
            'equipo_descripcion' => 'nullable|string|max:255|not_regex:/[*<>{}|]/',
            'equipo_categoria'   => 'nullable|string|max:50|not_regex:/[*<>{}|]/',
        ], [
            'equipo_nombre.required'       => 'El campo nombre es obligatorio.',
            'equipo_nombre.not_regex'      => 'El nombre contiene caracteres no permitidos.',
            'equipo_descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
        ]);

        $equipo->update([
            'equipo_nombre'      => $r->equipo_nombre,
            'equipo_descripcion' => $r->equipo_descripcion,
            'equipo_categoria'   => $r->equipo_categoria,
        ]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $equipo,
        ], 200);
    }

    public function cambiarEstado($id)
    {
        $equipo = EquipoTrabajo::find($id);
        if (!$equipo) {
            return response()->json(['mensaje' => 'Equipo de Trabajo no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($equipo->equipo_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $equipo->update(['equipo_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Equipo de Trabajo activado con éxito.' : 'Equipo de Trabajo desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
