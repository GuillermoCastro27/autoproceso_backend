<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FuncionarioController extends Controller
{
    public function read()
    {
        return DB::table('v_funcionarios')->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'fun_nom'         => 'required|string|max:100',
            'fun_apellido'    => 'required|string|max:100',
            'fun_ci'          => ['required', 'string', 'max:30', Rule::unique('funcionario', 'fun_ci')->whereNull('deleted_at')],
            'fun_direccion'   => 'required|string|max:200',
            'fun_telefono'    => 'required|string|max:30',
            'fun_correo'      => 'required|email|max:100',
            'pais_id'         => 'required|integer|exists:paises,id',
            'ciudad_id'       => 'required|integer|exists:ciudades,id',
            'nacionalidad_id' => 'required|integer|exists:nacionalidad,id',
        ], [
            'fun_nom.required'         => 'El nombre es obligatorio.',
            'fun_apellido.required'    => 'El apellido es obligatorio.',
            'fun_ci.required'          => 'La cédula de identidad es obligatoria.',
            'fun_ci.unique'            => 'Ya existe un funcionario con esa cédula de identidad.',
            'fun_direccion.required'   => 'La dirección es obligatoria.',
            'fun_telefono.required'    => 'El teléfono es obligatorio.',
            'fun_correo.required'      => 'El correo electrónico es obligatorio.',
            'fun_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'ciudad_id.required'       => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required' => 'Debe seleccionar una nacionalidad.',
        ]);

        $funcionario = Funcionario::create([
            'fun_nom'         => $r->fun_nom,
            'fun_apellido'    => $r->fun_apellido,
            'fun_ci'          => $r->fun_ci,
            'fun_direccion'   => $r->fun_direccion,
            'fun_telefono'    => $r->fun_telefono,
            'fun_correo'      => $r->fun_correo,
            'pais_id'         => $r->pais_id,
            'ciudad_id'       => $r->ciudad_id,
            'nacionalidad_id' => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Funcionario creado con éxito',
            'tipo'     => 'success',
            'registro' => $funcionario,
        ]);
    }

    public function update(Request $r, $id)
    {
        $funcionario = Funcionario::find($id);
        if (!$funcionario) {
            return response()->json(['mensaje' => 'Funcionario no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'fun_nom'         => 'required|string|max:100',
            'fun_apellido'    => 'required|string|max:100',
            'fun_ci'          => ['required', 'string', 'max:30', Rule::unique('funcionario', 'fun_ci')->ignore($id)->whereNull('deleted_at')],
            'fun_direccion'   => 'required|string|max:200',
            'fun_telefono'    => 'required|string|max:30',
            'fun_correo'      => 'required|email|max:100',
            'pais_id'         => 'required|integer|exists:paises,id',
            'ciudad_id'       => 'required|integer|exists:ciudades,id',
            'nacionalidad_id' => 'required|integer|exists:nacionalidad,id',
        ], [
            'fun_nom.required'         => 'El nombre es obligatorio.',
            'fun_apellido.required'    => 'El apellido es obligatorio.',
            'fun_ci.required'          => 'La cédula de identidad es obligatoria.',
            'fun_ci.unique'            => 'Ya existe otro funcionario con esa cédula de identidad.',
            'fun_direccion.required'   => 'La dirección es obligatoria.',
            'fun_telefono.required'    => 'El teléfono es obligatorio.',
            'fun_correo.required'      => 'El correo electrónico es obligatorio.',
            'fun_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'ciudad_id.required'       => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required' => 'Debe seleccionar una nacionalidad.',
        ]);

        $funcionario->update([
            'fun_nom'         => $r->fun_nom,
            'fun_apellido'    => $r->fun_apellido,
            'fun_ci'          => $r->fun_ci,
            'fun_direccion'   => $r->fun_direccion,
            'fun_telefono'    => $r->fun_telefono,
            'fun_correo'      => $r->fun_correo,
            'pais_id'         => $r->pais_id,
            'ciudad_id'       => $r->ciudad_id,
            'nacionalidad_id' => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Funcionario actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $funcionario,
        ]);
    }

    public function destroy($id)
    {
        $funcionario = Funcionario::find($id);
        if (!$funcionario) {
            return response()->json(['mensaje' => 'Funcionario no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $funcionario->delete();
            return response()->json(['mensaje' => 'Funcionario eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el funcionario porque tiene registros asociados en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }

    public function buscar(Request $r)
    {
        return DB::select("
            SELECT
                f.id,
                f.fun_ci,
                f.fun_nom || ' ' || f.fun_apellido AS fun_nombre_completo,
                f.fun_correo,
                f.fun_telefono
            FROM funcionario f
            WHERE f.deleted_at IS NULL
              AND (f.fun_nom ILIKE ? OR f.fun_apellido ILIKE ? OR f.fun_ci ILIKE ?)
            ORDER BY f.fun_nom
            LIMIT 20
        ", ["%{$r->q}%", "%{$r->q}%", "%{$r->q}%"]);
    }
}
