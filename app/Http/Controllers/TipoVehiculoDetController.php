<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculoDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoVehiculoDetController extends Controller
{
    public function buscar(Request $r)
    {
        $q = $r->q ?? '';
        $like = "%{$q}%";
        return DB::select("
            SELECT
                d.id,
                d.tipo_vehiculo_id,
                d.tv_det_placa,
                d.tv_det_num_chasis,
                d.tv_det_num_motor,
                t.tip_veh_nombre,
                COALESCE(m.marc_nom,  '') AS marc_nom,
                COALESCE(mo.modelo_nom,'') AS modelo_nom,
                t.tv_anio,
                t.tv_color
            FROM tipo_vehiculo_det d
            JOIN tipo_vehiculo t   ON t.id  = d.tipo_vehiculo_id
            LEFT JOIN marca m     ON m.id  = t.marca_id
            LEFT JOIN modelo mo   ON mo.id = t.modelo_id
            WHERE d.tv_det_placa ILIKE ?
               OR t.tip_veh_nombre ILIKE ?
               OR m.marc_nom ILIKE ?
            ORDER BY d.tv_det_placa
            LIMIT 15
        ", [$like, $like, $like]);
    }

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
