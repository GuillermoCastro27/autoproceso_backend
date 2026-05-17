<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function read()
    {
        return DB::table('v_clientes')->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'cli_nombre'      => 'required|string|max:100',
            'cli_apellido'    => 'required|string|max:100',
            'cli_ruc'         => ['required', 'string', 'max:30', Rule::unique('clientes', 'cli_ruc')->whereNull('deleted_at')],
            'cli_direccion'   => 'required|string|max:200',
            'cli_telefono'    => 'required|string|max:30',
            'cli_correo'      => 'required|email|max:100',
            'pais_id'         => 'required|integer|exists:paises,id',
            'ciudad_id'       => 'required|integer|exists:ciudades,id',
            'nacionalidad_id' => 'required|integer|exists:nacionalidad,id',
        ], [
            'cli_nombre.required'      => 'El nombre es obligatorio.',
            'cli_apellido.required'    => 'El apellido es obligatorio.',
            'cli_ruc.required'         => 'El RUC/CI es obligatorio.',
            'cli_ruc.unique'           => 'Ya existe un cliente con ese RUC/CI.',
            'cli_direccion.required'   => 'La dirección es obligatoria.',
            'cli_telefono.required'    => 'El teléfono es obligatorio.',
            'cli_correo.required'      => 'El correo electrónico es obligatorio.',
            'cli_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'ciudad_id.required'       => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required' => 'Debe seleccionar una nacionalidad.',
        ]);

        $cliente = Cliente::create([
            'cli_nombre'      => $r->cli_nombre,
            'cli_apellido'    => $r->cli_apellido,
            'cli_ruc'         => $r->cli_ruc,
            'cli_direccion'   => $r->cli_direccion,
            'cli_telefono'    => $r->cli_telefono,
            'cli_correo'      => $r->cli_correo,
            'pais_id'         => $r->pais_id,
            'ciudad_id'       => $r->ciudad_id,
            'nacionalidad_id' => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Cliente creado con éxito',
            'tipo'     => 'success',
            'registro' => $cliente,
        ]);
    }

    public function update(Request $r, $id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'cli_nombre'      => 'required|string|max:100',
            'cli_apellido'    => 'required|string|max:100',
            'cli_ruc'         => ['required', 'string', 'max:30', Rule::unique('clientes', 'cli_ruc')->ignore($id)->whereNull('deleted_at')],
            'cli_direccion'   => 'required|string|max:200',
            'cli_telefono'    => 'required|string|max:30',
            'cli_correo'      => 'required|email|max:100',
            'pais_id'         => 'required|integer|exists:paises,id',
            'ciudad_id'       => 'required|integer|exists:ciudades,id',
            'nacionalidad_id' => 'required|integer|exists:nacionalidad,id',
        ], [
            'cli_nombre.required'      => 'El nombre es obligatorio.',
            'cli_apellido.required'    => 'El apellido es obligatorio.',
            'cli_ruc.required'         => 'El RUC/CI es obligatorio.',
            'cli_ruc.unique'           => 'Ya existe otro cliente con ese RUC/CI.',
            'cli_direccion.required'   => 'La dirección es obligatoria.',
            'cli_telefono.required'    => 'El teléfono es obligatorio.',
            'cli_correo.required'      => 'El correo electrónico es obligatorio.',
            'cli_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'ciudad_id.required'       => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required' => 'Debe seleccionar una nacionalidad.',
        ]);

        $cliente->update([
            'cli_nombre'      => $r->cli_nombre,
            'cli_apellido'    => $r->cli_apellido,
            'cli_ruc'         => $r->cli_ruc,
            'cli_direccion'   => $r->cli_direccion,
            'cli_telefono'    => $r->cli_telefono,
            'cli_correo'      => $r->cli_correo,
            'pais_id'         => $r->pais_id,
            'ciudad_id'       => $r->ciudad_id,
            'nacionalidad_id' => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Cliente actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $cliente,
        ]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $cliente->delete();
            return response()->json(['mensaje' => 'Cliente eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el cliente porque tiene registros asociados en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }

    public function buscar(Request $r)
    {
        $q = '%' . $r->cli_nombre . '%';
        return DB::select(
            "SELECT c.*, c.id AS clientes_id FROM clientes c WHERE (cli_nombre ILIKE ? OR cli_ruc ILIKE ?) AND c.deleted_at IS NULL",
            [$q, $q]
        );
    }
}
