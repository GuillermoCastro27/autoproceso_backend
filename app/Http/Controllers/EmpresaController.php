<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    public function read()
    {
        return Empresa::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'emp_razon_social' => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('empresa')
                        ->whereRaw('LOWER(emp_razon_social) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una empresa con esa razón social.');
                    }
                },
            ],
            'emp_direccion' => 'required|string|max:300',
            'emp_telefono'  => 'required|string|max:30',
            'emp_correo'    => 'required|email|max:100',
        ], [
            'emp_razon_social.required'  => 'La razón social es obligatoria.',
            'emp_razon_social.not_regex' => 'La razón social contiene caracteres no permitidos.',
            'emp_direccion.required'     => 'La dirección es obligatoria.',
            'emp_telefono.required'      => 'El teléfono es obligatorio.',
            'emp_correo.required'        => 'El correo electrónico es obligatorio.',
            'emp_correo.email'           => 'El correo no tiene un formato válido.',
        ]);

        $empresa = Empresa::create([
            'emp_razon_social' => $r->emp_razon_social,
            'emp_direccion'    => $r->emp_direccion,
            'emp_telefono'     => $r->emp_telefono,
            'emp_correo'       => $r->emp_correo,
        ]);

        return response()->json([
            'mensaje'  => 'Empresa creada con éxito',
            'tipo'     => 'success',
            'registro' => $empresa,
        ]);
    }

    public function update(Request $r, $id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'emp_razon_social' => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($id) {
                    $existe = \DB::table('empresa')
                        ->whereRaw('LOWER(emp_razon_social) = LOWER(?)', [trim($value)])
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otra empresa con esa razón social.');
                    }
                },
            ],
            'emp_direccion' => 'required|string|max:300',
            'emp_telefono'  => 'required|string|max:30',
            'emp_correo'    => 'required|email|max:100',
        ], [
            'emp_razon_social.required'  => 'La razón social es obligatoria.',
            'emp_razon_social.not_regex' => 'La razón social contiene caracteres no permitidos.',
            'emp_direccion.required'     => 'La dirección es obligatoria.',
            'emp_telefono.required'      => 'El teléfono es obligatorio.',
            'emp_correo.required'        => 'El correo electrónico es obligatorio.',
            'emp_correo.email'           => 'El correo no tiene un formato válido.',
        ]);

        $empresa->update([
            'emp_razon_social' => $r->emp_razon_social,
            'emp_direccion'    => $r->emp_direccion,
            'emp_telefono'     => $r->emp_telefono,
            'emp_correo'       => $r->emp_correo,
        ]);

        return response()->json([
            'mensaje'  => 'Empresa actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $empresa,
        ]);
    }

    public function cambiarEstado($id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($empresa->emp_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $empresa->update(['emp_estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Empresa activada con éxito.' : 'Empresa desactivada con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
