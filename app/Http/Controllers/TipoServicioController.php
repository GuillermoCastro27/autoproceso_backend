<?php

namespace App\Http\Controllers;

use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoServicioController extends Controller
{
    // 📋 Listar todos
    public function read()
    {
        return response()->json(
            TipoServicio::select('id as tipo_servicio_id', 'tipo_serv_nombre as tipo_serv_nombre','tip_serv_precio as tip_serv_precio')->get()
        );
    }

    // 🆕 Crear nuevo tipo de servicio
    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'tipo_serv_nombre' => 'required|string|max:100|unique:tipo_servicio,tipo_serv_nombre',
            'tip_serv_precio' => 'required|integer'
        ], [
            'tipo_serv_nombre.required' => 'El campo nombre es obligatorio.',
            'tipo_serv_nombre.unique' => 'El tipo de servicio ya existe.',
            'tip_serv_precio' => 'El campo precio es obligatorio',
        ]);

        $tiposervicio = TipoServicio::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $tiposervicio
        ], 200);
    }

    // ✏️ Actualizar tipo de servicio
    public function update(Request $r, $id)
    {
        $tiposervicio = TipoServicio::find($id);
        if (!$tiposervicio) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'tipo_serv_nombre' => 'required|string|max:100|unique:tipo_servicio,tipo_serv_nombre',
            'tip_serv_precio' => 'required|integer'
        ], [
            'tipo_serv_nombre.required' => 'El campo nombre es obligatorio.',
            'tipo_serv_nombre.unique' => 'El tipo de servicio ya existe.',
            'tip_serv_precio' => 'El campo precio es obligatorio',
        ]);

        $tiposervicio->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $tiposervicio
        ], 200);
    }

    // 🗑️ Eliminar tipo de servicio
    public function destroy($id)
    {
        $tiposervicio = TipoServicio::find($id);
        if (!$tiposervicio) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $tiposervicio->delete();

        return response()->json([
            'mensaje' => 'Registro eliminado con éxito',
            'tipo' => 'success',
        ], 200);
    }
}
