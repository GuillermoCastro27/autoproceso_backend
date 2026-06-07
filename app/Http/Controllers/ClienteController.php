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

    private function validationRules(string $tipo, ?int $ignoreId = null): array
    {
        $rules = [
            'cli_tipo_persona' => 'required|in:FISICA,JURIDICA',
            'cli_ruc'          => [
                'required', 'string', 'max:20',
                'regex:/^([A-Za-z]{1,2}\d{6,9}|\d{6,8}(-\d{1,2})?)$/',
                Rule::unique('clientes', 'cli_ruc')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'cli_direccion'   => 'required|string|max:200',
            'cli_telefono'    => 'required|string|max:30',
            'cli_correo'      => 'required|email|max:100',
            'pais_id'         => 'required|integer|exists:paises,id',
            'ciudad_id'       => 'required|integer|exists:ciudades,id',
            'nacionalidad_id' => 'required|integer|exists:nacionalidad,id',
        ];

        if ($tipo === 'JURIDICA') {
            $rules['cli_razon_social'] = ['required', 'string', 'max:200', 'not_regex:/[*<>{}|]/'];
            $rules['cli_nombre']       = ['nullable', 'string', 'max:100', 'not_regex:/[*<>{}|]/'];
            $rules['cli_apellido']     = ['nullable', 'string', 'max:100', 'not_regex:/[*<>{}|]/'];
        } else {
            $rules['cli_nombre']       = ['required', 'string', 'max:100', 'not_regex:/[*<>{}|]/'];
            $rules['cli_apellido']     = ['required', 'string', 'max:100', 'not_regex:/[*<>{}|]/'];
            $rules['cli_razon_social'] = 'nullable|string|max:200';
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        return [
            'cli_tipo_persona.required'  => 'El tipo de persona es obligatorio.',
            'cli_tipo_persona.in'        => 'El tipo de persona debe ser FISICA o JURIDICA.',
            'cli_nombre.required'        => 'El nombre es obligatorio.',
            'cli_nombre.not_regex'       => 'El nombre contiene caracteres no permitidos.',
            'cli_apellido.required'      => 'El apellido es obligatorio.',
            'cli_apellido.not_regex'     => 'El apellido contiene caracteres no permitidos.',
            'cli_razon_social.required'  => 'La razón social es obligatoria para persona jurídica.',
            'cli_razon_social.not_regex' => 'La razón social contiene caracteres no permitidos.',
            'cli_ruc.required'           => 'El Nro. Documento es obligatorio.',
            'cli_ruc.regex'              => 'Formato inválido. Use CI (1234567), RUC (80123456-7) o Pasaporte (AA123456).',
            'cli_ruc.unique'             => 'Ya existe un cliente con ese Nro. Documento.',
            'cli_direccion.required'     => 'La dirección es obligatoria.',
            'cli_telefono.required'      => 'El teléfono es obligatorio.',
            'cli_correo.required'        => 'El correo electrónico es obligatorio.',
            'cli_correo.email'           => 'El correo no tiene un formato válido.',
            'pais_id.required'           => 'Debe seleccionar un país.',
            'ciudad_id.required'         => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required'   => 'Debe seleccionar una nacionalidad.',
        ];
    }

    public function store(Request $r)
    {
        $tipo = $r->cli_tipo_persona ?? 'FISICA';

        $r->validate($this->validationRules($tipo), $this->validationMessages());

        $cliente = Cliente::create([
            'cli_tipo_persona' => $tipo,
            'cli_razon_social' => $tipo === 'JURIDICA' ? $r->cli_razon_social : null,
            'cli_nombre'       => $r->cli_nombre,
            'cli_apellido'     => $r->cli_apellido,
            'cli_ruc'          => $r->cli_ruc,
            'cli_direccion'    => $r->cli_direccion,
            'cli_telefono'     => $r->cli_telefono,
            'cli_correo'       => $r->cli_correo,
            'pais_id'          => $r->pais_id,
            'ciudad_id'        => $r->ciudad_id,
            'nacionalidad_id'  => $r->nacionalidad_id,
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

        $tipo = $r->cli_tipo_persona ?? 'FISICA';

        $r->validate($this->validationRules($tipo, $id), $this->validationMessages());

        $cliente->update([
            'cli_tipo_persona' => $tipo,
            'cli_razon_social' => $tipo === 'JURIDICA' ? $r->cli_razon_social : null,
            'cli_nombre'       => $r->cli_nombre,
            'cli_apellido'     => $r->cli_apellido,
            'cli_ruc'          => $r->cli_ruc,
            'cli_direccion'    => $r->cli_direccion,
            'cli_telefono'     => $r->cli_telefono,
            'cli_correo'       => $r->cli_correo,
            'pais_id'          => $r->pais_id,
            'ciudad_id'        => $r->ciudad_id,
            'nacionalidad_id'  => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Cliente actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $cliente,
        ]);
    }

    public function cambiarEstado($id)
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['mensaje' => 'Cliente no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = $cliente->cli_estado === 'activo' ? 'inactivo' : 'activo';
        $cliente->update(['cli_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Cliente activado con éxito.' : 'Cliente desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }

    public function buscar(Request $r)
    {
        $q = '%' . ($r->cli_nombre ?? '') . '%';
        return DB::select("
            SELECT
                c.id AS clientes_id,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,
                c.cli_tipo_persona,
                c.cli_razon_social
            FROM clientes c
            WHERE (
                c.cli_nombre    ILIKE ?
                OR c.cli_apellido ILIKE ?
                OR c.cli_ruc      ILIKE ?
                OR c.cli_razon_social ILIKE ?
            )
            AND c.deleted_at IS NULL
            AND c.cli_estado = 'activo'
            ORDER BY c.cli_nombre
        ", [$q, $q, $q, $q]);
    }
}
