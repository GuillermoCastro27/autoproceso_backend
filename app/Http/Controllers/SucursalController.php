<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    public function read()
    {
        return response()->json(DB::select('
            SELECT s.*, e.emp_razon_social
            FROM sucursal s
            INNER JOIN empresa e ON e.id = s.empresa_id
        '));
    }

    public function store(Request $r)
    {
        $r->validate([
            'empresa_id'      => 'required|integer|exists:empresa,id',
            'suc_razon_social'=> [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('sucursal')
                        ->whereRaw('LOWER(suc_razon_social) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una sucursal con esa razón social.');
                    }
                },
            ],
            'suc_direccion'   => 'required|string|max:300',
            'suc_telefono'    => 'required|string|max:30',
            'suc_correo'      => 'required|email|max:100',
        ], [
            'empresa_id.required'       => 'Debe seleccionar una empresa.',
            'empresa_id.exists'         => 'La empresa seleccionada no existe.',
            'suc_razon_social.required' => 'La razón social de la sucursal es obligatoria.',
            'suc_direccion.required'    => 'La dirección es obligatoria.',
            'suc_telefono.required'     => 'El teléfono es obligatorio.',
            'suc_correo.required'       => 'El correo electrónico es obligatorio.',
            'suc_correo.email'          => 'El correo no tiene un formato válido.',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id'       => $r->empresa_id,
            'suc_razon_social' => $r->suc_razon_social,
            'suc_direccion'    => $r->suc_direccion,
            'suc_telefono'     => $r->suc_telefono,
            'suc_correo'       => $r->suc_correo,
        ]);

        return response()->json([
            'mensaje'  => 'Sucursal creada con éxito',
            'tipo'     => 'success',
            'registro' => $sucursal,
        ]);
    }

    public function update(Request $r, $id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['mensaje' => 'Sucursal no encontrada', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'empresa_id'      => 'required|integer|exists:empresa,id',
            'suc_razon_social'=> [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('sucursal')
                        ->whereRaw('LOWER(suc_razon_social) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra sucursal con esa razón social.');
                    }
                },
            ],
            'suc_direccion'   => 'required|string|max:300',
            'suc_telefono'    => 'required|string|max:30',
            'suc_correo'      => 'required|email|max:100',
        ], [
            'empresa_id.required'       => 'Debe seleccionar una empresa.',
            'empresa_id.exists'         => 'La empresa seleccionada no existe.',
            'suc_razon_social.required' => 'La razón social de la sucursal es obligatoria.',
            'suc_direccion.required'    => 'La dirección es obligatoria.',
            'suc_telefono.required'     => 'El teléfono es obligatorio.',
            'suc_correo.required'       => 'El correo electrónico es obligatorio.',
            'suc_correo.email'          => 'El correo no tiene un formato válido.',
        ]);

        $sucursal->update([
            'empresa_id'       => $r->empresa_id,
            'suc_razon_social' => $r->suc_razon_social,
            'suc_direccion'    => $r->suc_direccion,
            'suc_telefono'     => $r->suc_telefono,
            'suc_correo'       => $r->suc_correo,
        ]);

        return response()->json([
            'mensaje'  => 'Sucursal actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $sucursal,
        ]);
    }

    public function cambiarEstado($id)
    {
        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json(['mensaje' => 'Sucursal no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($sucursal->suc_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $sucursal->update(['suc_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Sucursal activada con éxito.' : 'Sucursal desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
