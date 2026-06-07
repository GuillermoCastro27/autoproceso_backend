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
                'tipo_vehiculo.tv_uso',
                'tipo_vehiculo.tv_anio',
                'tipo_vehiculo.tv_color',
                'tipo_vehiculo.marca_id',
                'tipo_vehiculo.modelo_id',
                'tipo_vehiculo.tip_veh_estado',
                'marca.marc_nom as marca_nombre',
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
        $datosValidados = $r->validate([
            'tip_veh_nombre'       => [
                'required', 'string', 'max:50', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('tipo_vehiculo')
                        ->whereRaw('LOWER(tip_veh_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un tipo de vehículo con ese nombre.');
                    }
                },
            ],
            'tv_uso'               => 'required|in:SERVICIO,EMPRESA',
            'tip_veh_capacidad'    => 'nullable|integer|min:1',
            'tip_veh_combustible'  => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'tip_veh_categoria'    => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'tip_veh_observacion'  => ['nullable', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'tv_anio'              => 'nullable|integer|min:1900|max:2100',
            'tv_color'             => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'marca_id'             => 'required|exists:marca,id',
            'modelo_id'            => 'required|exists:modelo,id'
        ], [
            'tip_veh_nombre.required'       => 'El nombre del tipo de vehículo es obligatorio.',
            'tv_uso.required'               => 'El uso es obligatorio.',
            'tv_uso.in'                     => 'El uso debe ser SERVICIO o EMPRESA.',
            'tip_veh_combustible.not_regex' => 'El combustible contiene caracteres no permitidos.',
            'tip_veh_categoria.not_regex'   => 'La categoría contiene caracteres no permitidos.',
            'tip_veh_observacion.not_regex' => 'La observación contiene caracteres no permitidos.',
            'tv_color.not_regex'            => 'El color contiene caracteres no permitidos.',
            'marca_id.required'             => 'La marca es obligatoria.',
            'marca_id.exists'               => 'La marca seleccionada no es válida.',
            'modelo_id.required'            => 'El modelo es obligatorio.',
            'modelo_id.exists'              => 'El modelo seleccionado no es válido.',
            'tv_anio.integer'               => 'El año debe ser un número entero.',
            'tv_anio.min'                   => 'El año no puede ser menor a 1900.',
            'tv_anio.max'                   => 'El año no puede ser mayor a 2100.',
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

        $datosValidados = $r->validate([
            'tip_veh_nombre'       => [
                'required', 'string', 'max:50', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('tipo_vehiculo')
                        ->whereRaw('LOWER(tip_veh_nombre) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro tipo de vehículo con ese nombre.');
                    }
                },
            ],
            'tv_uso'               => 'required|in:SERVICIO,EMPRESA',
            'tip_veh_capacidad'    => 'nullable|integer|min:1',
            'tip_veh_combustible'  => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'tip_veh_categoria'    => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'tip_veh_observacion'  => ['nullable', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'tv_anio'              => 'nullable|integer|min:1900|max:2100',
            'tv_color'             => ['nullable', 'string', 'max:30', 'not_regex:/[*<>{}|]/'],
            'marca_id'             => 'required|exists:marca,id',
            'modelo_id'            => 'required|exists:modelo,id'
        ], [
            'tip_veh_nombre.required'       => 'El nombre del tipo de vehículo es obligatorio.',
            'tv_uso.required'               => 'El uso es obligatorio.',
            'tv_uso.in'                     => 'El uso debe ser SERVICIO o EMPRESA.',
            'tip_veh_combustible.not_regex' => 'El combustible contiene caracteres no permitidos.',
            'tip_veh_categoria.not_regex'   => 'La categoría contiene caracteres no permitidos.',
            'tip_veh_observacion.not_regex' => 'La observación contiene caracteres no permitidos.',
            'tv_color.not_regex'            => 'El color contiene caracteres no permitidos.',
            'marca_id.required'             => 'La marca es obligatoria.',
            'marca_id.exists'               => 'La marca seleccionada no es válida.',
            'modelo_id.required'            => 'El modelo es obligatorio.',
            'modelo_id.exists'              => 'El modelo seleccionado no es válido.',
            'tv_anio.integer'               => 'El año debe ser un número entero.',
            'tv_anio.min'                   => 'El año no puede ser menor a 1900.',
            'tv_anio.max'                   => 'El año no puede ser mayor a 2100.',
        ]);

        $tipovehiculo->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro actualizado con éxito',
            'tipo' => 'success',
            'registro' => $tipovehiculo
        ], 200);
    }
    public function cambiarEstado($id)
    {
        $tipovehiculo = TipoVehiculo::find($id);
        if (!$tipovehiculo) {
            return response()->json(['mensaje' => 'Tipo de Vehículo no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($tipovehiculo->tip_veh_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $tipovehiculo->update(['tip_veh_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Tipo de Vehículo activado con éxito.' : 'Tipo de Vehículo desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
    public function buscarPorMarca(Request $request)
    {
        $marca_id = $request->marca_id;
        $texto    = $request->texto ?? '';
        $uso      = $request->uso;   // opcional: 'SERVICIO' o 'EMPRESA'

        $query = TipoVehiculo::select(
                'tipo_vehiculo.id as tipo_vehiculo_id',
                'tipo_vehiculo.tip_veh_nombre',
                'tipo_vehiculo.tip_veh_capacidad',
                'tipo_vehiculo.tip_veh_combustible',
                'tipo_vehiculo.tip_veh_categoria',
                'tipo_vehiculo.tip_veh_observacion',
                'tipo_vehiculo.tv_uso',
                'marca.marc_nom as marca_nombre',
                'modelo.modelo_nom as modelo_nombre',
                'modelo.modelo_año as modelo_año',
                'tipo_vehiculo.marca_id',
                'tipo_vehiculo.modelo_id'
            )
            ->join('marca', 'tipo_vehiculo.marca_id', '=', 'marca.id')
            ->join('modelo', 'tipo_vehiculo.modelo_id', '=', 'modelo.id')
            ->where('tipo_vehiculo.marca_id', $marca_id)
            ->where(function ($q) use ($texto) {
                $q->where('tipo_vehiculo.tip_veh_nombre', 'ILIKE', "%$texto%")
                  ->orWhere('modelo.modelo_nom', 'ILIKE', "%$texto%");
            })
            ->where('tipo_vehiculo.tip_veh_estado', 'activo');

        if ($uso) {
            $query->where('tipo_vehiculo.tv_uso', $uso);
        }

        return response()->json($query->orderBy('tipo_vehiculo.tip_veh_nombre')->get());
    }
}
