<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\TipoVehiculo;

use Illuminate\Http\Request;

class TipoVehiculoController extends Controller
{
    public function read()
{
    return response()->json(
        TipoVehiculo::select(
            'tipo_vehiculo.id as tipo_vehiculo_id',
            'tipo_vehiculo.tip_veh_nombre',
            'tipo_vehiculo.tip_veh_capacidad',
            'tipo_vehiculo.tip_veh_combustible',
            'tipo_vehiculo.tip_veh_categoria',
            'tipo_vehiculo.tip_veh_observacion',

            // Marca (evita undefined)
            'marca.marc_nom as marca_nombre',

            // Modelo (evita undefined)
            'modelo.modelo_nom as modelo_nombre',
            'modelo.modelo_año as modelo_año'
        )
        ->join('marca', 'tipo_vehiculo.marca_id', '=', 'marca.id')
        ->join('modelo', 'tipo_vehiculo.modelo_id', '=', 'modelo.id')
        ->get()
    );
}
    public function store(Request $r)
    {
        // ✅ ÚNICA VALIDACIÓN
        $datosValidados = $r->validate([
            'tip_veh_nombre'       => 'required|string|max:50|unique:tipo_vehiculo,tip_veh_nombre',
            'tip_veh_capacidad'    => 'nullable|integer',
            'tip_veh_combustible'  => 'nullable|string|max:30',
            'tip_veh_categoria'    => 'nullable|string|max:30',
            'tip_veh_observacion'  => 'nullable|string|max:200',
            'marca_id'             => 'required|exists:marca,id',
            'modelo_id'            => 'required|exists:modelo,id'
        ], [
            'tip_veh_nombre.required' => 'El nombre del tipo de vehículo es obligatorio.',
            'tip_veh_nombre.unique'   => 'Ya existe un tipo de vehículo con ese nombre.',
            'marca_id.required'       => 'La marca es obligatoria.',
            'marca_id.exists'         => 'La marca seleccionada no es válida.',
            'modelo_id.required'      => 'El modelo es obligatorio.',
            'modelo_id.exists'        => 'El modelo seleccionado no es válido.'
        ]);

        $tipovehiculo = TipoVehiculo::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $tipovehiculo
        ], 200);
    }
    public function update(Request $r, $id)
    {
        $tipovehiculo = TipoVehiculo::find($id);
        if (!$tipovehiculo) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // ✅ ÚNICA VALIDACIÓN
        $datosValidados = $r->validate([
            'tip_veh_nombre'       => 'required|string|max:50|unique:tipo_vehiculo,tip_veh_nombre,' . $id,
            'tip_veh_capacidad'    => 'nullable|integer',
            'tip_veh_combustible'  => 'nullable|string|max:30',
            'tip_veh_categoria'    => 'nullable|string|max:30',
            'tip_veh_observacion'  => 'nullable|string|max:200',
            'marca_id'             => 'required|exists:marca,id',
            'modelo_id'            => 'required|exists:modelo,id'
        ], [
            'tip_veh_nombre.required' => 'El nombre del tipo de vehículo es obligatorio.',
            'tip_veh_nombre.unique'   => 'Ya existe un tipo de vehículo con ese nombre.',
            'marca_id.required'       => 'La marca es obligatoria.',
            'marca_id.exists'         => 'La marca seleccionada no es válida.',
            'modelo_id.required'      => 'El modelo es obligatorio.',
            'modelo_id.exists'        => 'El modelo seleccionado no es válido.'
        ]);

        $tipovehiculo->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro actualizado con éxito',
            'tipo' => 'success',
            'registro' => $tipovehiculo
        ], 200);
    }
    public function destroy($id)
    {
        $tipovehiculo = TipoVehiculo::find($id);
        if (!$tipovehiculo) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        try {
            $tipovehiculo->delete();
            return response()->json([
                'mensaje' => 'Registro eliminado con éxito',
                'tipo' => 'success'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el registro porque está relacionado con otros datos.',
                'tipo' => 'error'
            ], 400);
        }
    }
}
