<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculoDet;
use Illuminate\Http\Request;

class TipoVehiculoDetController extends Controller
{
    public function read($tipo_vehiculo_id)
    {
        return response()->json(
            TipoVehiculoDet::where('tipo_vehiculo_id', $tipo_vehiculo_id)
                ->orderBy('id')
                ->get()
        );
    }

    public function store(Request $r)
    {
        $r->validate([
            'tipo_vehiculo_id'  => 'required|integer|exists:tipo_vehiculo,id',
            'tv_det_placa'      => ['nullable', 'string', 'max:20', 'not_regex:/[*<>{}|]/'],
            'tv_det_num_chasis' => ['nullable', 'string', 'max:50', 'not_regex:/[*<>{}|]/'],
            'tv_det_num_motor'  => ['nullable', 'string', 'max:50', 'not_regex:/[*<>{}|]/'],
        ], [
            'tipo_vehiculo_id.required' => 'Debe seleccionar un tipo de vehículo.',
            'tipo_vehiculo_id.exists'   => 'El tipo de vehículo no es válido.',
            'tv_det_placa.max'          => 'La placa no puede superar 20 caracteres.',
            'tv_det_num_chasis.max'     => 'El número de chasis no puede superar 50 caracteres.',
            'tv_det_num_motor.max'      => 'El número de motor no puede superar 50 caracteres.',
        ]);

        $det = TipoVehiculoDet::create([
            'tipo_vehiculo_id'  => $r->tipo_vehiculo_id,
            'tv_det_placa'      => $r->tv_det_placa,
            'tv_det_num_chasis' => $r->tv_det_num_chasis,
            'tv_det_num_motor'  => $r->tv_det_num_motor,
        ]);

        return response()->json([
            'mensaje'  => 'Vehículo registrado con éxito',
            'tipo'     => 'success',
            'registro' => $det,
        ]);
    }

    public function update(Request $r, $id)
    {
        $det = TipoVehiculoDet::find($id);
        if (!$det) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tv_det_placa'      => ['nullable', 'string', 'max:20', 'not_regex:/[*<>{}|]/'],
            'tv_det_num_chasis' => ['nullable', 'string', 'max:50', 'not_regex:/[*<>{}|]/'],
            'tv_det_num_motor'  => ['nullable', 'string', 'max:50', 'not_regex:/[*<>{}|]/'],
        ], [
            'tv_det_placa.max'      => 'La placa no puede superar 20 caracteres.',
            'tv_det_num_chasis.max' => 'El número de chasis no puede superar 50 caracteres.',
            'tv_det_num_motor.max'  => 'El número de motor no puede superar 50 caracteres.',
        ]);

        $det->tv_det_placa      = $r->tv_det_placa;
        $det->tv_det_num_chasis = $r->tv_det_num_chasis;
        $det->tv_det_num_motor  = $r->tv_det_num_motor;
        $det->save();

        return response()->json([
            'mensaje'  => 'Vehículo actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $det,
        ]);
    }

    public function destroy($id)
    {
        $det = TipoVehiculoDet::find($id);
        if (!$det) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $det->delete();

        return response()->json(['mensaje' => 'Vehículo eliminado con éxito', 'tipo' => 'success']);
    }
}
