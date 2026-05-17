<?php

namespace App\Http\Controllers;

use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoServicioController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoServicio::select('id as tipo_servicio_id', 'tipo_serv_nombre', 'tip_serv_precio')->get()
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'tipo_serv_nombre' => 'required|string|max:100|unique:tipo_servicio,tipo_serv_nombre',
            'tip_serv_precio'  => 'required|numeric|min:0',
        ], [
            'tipo_serv_nombre.required' => 'El nombre del tipo de servicio es obligatorio.',
            'tipo_serv_nombre.unique'   => 'Ya existe un tipo de servicio con ese nombre.',
            'tip_serv_precio.required'  => 'El precio base es obligatorio.',
            'tip_serv_precio.numeric'   => 'El precio debe ser un valor numérico.',
            'tip_serv_precio.min'       => 'El precio no puede ser negativo.',
        ]);

        $tiposervicio = TipoServicio::create([
            'tipo_serv_nombre' => $r->tipo_serv_nombre,
            'tip_serv_precio'  => $r->tip_serv_precio,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de servicio creado con éxito',
            'tipo'     => 'success',
            'registro' => $tiposervicio,
        ]);
    }

    public function update(Request $r, $id)
    {
        $tiposervicio = TipoServicio::find($id);
        if (!$tiposervicio) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tipo_serv_nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('tipo_servicio', 'tipo_serv_nombre')->ignore($id),
            ],
            'tip_serv_precio' => 'required|numeric|min:0',
        ], [
            'tipo_serv_nombre.required' => 'El nombre del tipo de servicio es obligatorio.',
            'tipo_serv_nombre.unique'   => 'Ya existe otro tipo de servicio con ese nombre.',
            'tip_serv_precio.required'  => 'El precio base es obligatorio.',
            'tip_serv_precio.numeric'   => 'El precio debe ser un valor numérico.',
            'tip_serv_precio.min'       => 'El precio no puede ser negativo.',
        ]);

        $tiposervicio->update([
            'tipo_serv_nombre' => $r->tipo_serv_nombre,
            'tip_serv_precio'  => $r->tip_serv_precio,
        ]);

        return response()->json([
            'mensaje'  => 'Tipo de servicio actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $tiposervicio,
        ]);
    }

    public function destroy($id)
    {
        $tiposervicio = TipoServicio::find($id);
        if (!$tiposervicio) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $tiposervicio->delete();
            return response()->json(['mensaje' => 'Tipo de servicio eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el tipo de servicio porque está siendo utilizado en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
