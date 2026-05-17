<?php

namespace App\Http\Controllers;

use App\Models\EquipoTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'equipo_nombre' => 'required|string|max:100|unique:equipo_trabajo,equipo_nombre',
            'equipo_descripcion' => 'nullable|string|max:255',
            'equipo_categoria' => 'nullable|string|max:50'
        ], [
            'equipo_nombre.required' => 'El campo nombre es obligatorio.',
            'equipo_nombre.unique' => 'Ya existe un equipo con este nombre.'
        ]);

        $equipo = EquipoTrabajo::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $equipo
        ]);
    }

    // Actualizar tipo de servicio
    public function update(Request $r, $id)
    {
        $equipo = EquipoTrabajo::find($id);
        if (!$equipo) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $r->validate([
            'equipo_nombre'      => ['required', 'string', 'max:100', Rule::unique('equipo_trabajo', 'equipo_nombre')->ignore($id)],
            'equipo_descripcion' => 'nullable|string|max:255',
            'equipo_categoria'   => 'nullable|string|max:50',
        ], [
            'equipo_nombre.required' => 'El campo nombre es obligatorio.',
            'equipo_nombre.unique'   => 'Ya existe otro equipo con ese nombre.',
        ]);

        $equipo->update([
            'equipo_nombre'      => $r->equipo_nombre,
            'equipo_descripcion' => $r->equipo_descripcion,
            'equipo_categoria'   => $r->equipo_categoria,
        ]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $equipo
        ], 200);
    }

    // 🗑️ Eliminar tipo de servicio
    public function destroy($id)
    {
        $equipo = EquipoTrabajo::find($id);
        if (!$equipo) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        try {
            $equipo->delete();
            return response()->json(['mensaje' => 'Equipo de trabajo eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el equipo porque tiene registros asociados en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
