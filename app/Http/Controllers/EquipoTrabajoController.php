<?php

namespace App\Http\Controllers;

use App\Models\EquipoTrabajo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $datosValidados = $r->validate([
            'equipo_nombre' => 'required|string|max:100|unique:equipo_trabajo,equipo_nombre',
            'equipo_descripcion' => 'nullable|string|max:255',
            'equipo_categoria' => 'nullable|string|max:50'
        ], [
            'equipo_nombre.required' => 'El campo nombre es obligatorio.',
            'equipo_nombre.unique' => 'Ya existe un equipo con este nombre.'
        ]);

        $equipo->update($datosValidados);

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

        $equipo->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success',
        ], 200);
    }
}
