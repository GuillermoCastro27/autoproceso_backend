<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function read()
    {
        return DB::table('v_proveedores')->get();
    }

    public function store(Request $r)
    {
        $r->validate([
            'prov_razonsocial' => 'required|string|max:200|not_regex:/[*<>{}|]/',
            'prov_ruc'         => ['required', 'string', 'max:20', 'regex:/^([A-Za-z]{1,2}\d{6,9}|\d{6,8}(-\d{1,2})?)$/', Rule::unique('proveedores', 'prov_ruc')->whereNull('deleted_at')],
            'prov_direccion'   => 'required|string|max:300',
            'prov_telefono'    => 'required|string|max:30',
            'prov_correo'      => 'required|email|max:100',
            'pais_id'          => 'required|integer|exists:paises,id',
            'ciudad_id'        => 'required|integer|exists:ciudades,id',
            'nacionalidad_id'  => 'required|integer|exists:nacionalidad,id',
        ], [
            'prov_razonsocial.required' => 'La razón social es obligatoria.',
            'prov_ruc.required'         => 'El Nro. Documento es obligatorio.',
            'prov_ruc.regex'            => 'Formato inválido. Use CI (1234567), RUC (80123456-7) o Pasaporte (AA123456).',
            'prov_ruc.unique'           => 'Ya existe un proveedor con ese Nro. Documento.',
            'prov_direccion.required'   => 'La dirección es obligatoria.',
            'prov_telefono.required'    => 'El teléfono es obligatorio.',
            'prov_correo.required'      => 'El correo electrónico es obligatorio.',
            'prov_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'          => 'Debe seleccionar un país.',
            'ciudad_id.required'        => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required'  => 'Debe seleccionar una nacionalidad.',
        ]);

        $proveedor = Proveedor::create([
            'prov_razonsocial' => $r->prov_razonsocial,
            'prov_ruc'         => $r->prov_ruc,
            'prov_direccion'   => $r->prov_direccion,
            'prov_telefono'    => $r->prov_telefono,
            'prov_correo'      => $r->prov_correo,
            'pais_id'          => $r->pais_id,
            'ciudad_id'        => $r->ciudad_id,
            'nacionalidad_id'  => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Proveedor creado con éxito',
            'tipo'     => 'success',
            'registro' => $proveedor,
        ]);
    }

    public function update(Request $r, $id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'prov_razonsocial' => 'required|string|max:200|not_regex:/[*<>{}|]/',
            'prov_ruc'         => ['required', 'string', 'max:20', 'regex:/^([A-Za-z]{1,2}\d{6,9}|\d{6,8}(-\d{1,2})?)$/', Rule::unique('proveedores', 'prov_ruc')->ignore($id)->whereNull('deleted_at')],
            'prov_direccion'   => 'required|string|max:300',
            'prov_telefono'    => 'required|string|max:30',
            'prov_correo'      => 'required|email|max:100',
            'pais_id'          => 'required|integer|exists:paises,id',
            'ciudad_id'        => 'required|integer|exists:ciudades,id',
            'nacionalidad_id'  => 'required|integer|exists:nacionalidad,id',
        ], [
            'prov_razonsocial.required' => 'La razón social es obligatoria.',
            'prov_ruc.required'         => 'El Nro. Documento es obligatorio.',
            'prov_ruc.regex'            => 'Formato inválido. Use CI (1234567), RUC (80123456-7) o Pasaporte (AA123456).',
            'prov_ruc.unique'           => 'Ya existe otro proveedor con ese Nro. Documento.',
            'prov_direccion.required'   => 'La dirección es obligatoria.',
            'prov_telefono.required'    => 'El teléfono es obligatorio.',
            'prov_correo.required'      => 'El correo electrónico es obligatorio.',
            'prov_correo.email'         => 'El correo no tiene un formato válido.',
            'pais_id.required'          => 'Debe seleccionar un país.',
            'ciudad_id.required'        => 'Debe seleccionar una ciudad.',
            'nacionalidad_id.required'  => 'Debe seleccionar una nacionalidad.',
        ]);

        $proveedor->update([
            'prov_razonsocial' => $r->prov_razonsocial,
            'prov_ruc'         => $r->prov_ruc,
            'prov_direccion'   => $r->prov_direccion,
            'prov_telefono'    => $r->prov_telefono,
            'prov_correo'      => $r->prov_correo,
            'pais_id'          => $r->pais_id,
            'ciudad_id'        => $r->ciudad_id,
            'nacionalidad_id'  => $r->nacionalidad_id,
        ]);

        return response()->json([
            'mensaje'  => 'Proveedor actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $proveedor,
        ]);
    }

    public function cambiarEstado($id)
    {
        $proveedor = Proveedor::find($id);
        if (!$proveedor) {
            return response()->json(['mensaje' => 'Proveedor no encontrado', 'tipo' => 'error'], 404);
        }

        $nuevoEstado = $proveedor->prov_estado === 'activo' ? 'inactivo' : 'activo';
        $proveedor->update(['prov_estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'Proveedor activado con éxito.' : 'Proveedor desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }

    public function buscar(Request $r)
    {
        $q = '%' . $r->prov_razonsocial . '%';
        return DB::select(
            "SELECT p.*, p.id AS proveedor_id FROM proveedores p WHERE (prov_razonsocial ILIKE ? OR prov_ruc ILIKE ?) AND p.deleted_at IS NULL AND p.prov_estado = 'activo'",
            [$q, $q]
        );
    }
}
