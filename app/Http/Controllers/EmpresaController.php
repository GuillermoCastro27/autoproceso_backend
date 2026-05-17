<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    public function read()
    {
        return Empresa::all();
    }

    public function store(Request $r)
    {
        $r->validate([
            'emp_razon_social' => 'required|string|max:200',
            'emp_direccion'    => 'required|string|max:300',
            'emp_telefono'     => 'required|string|max:30',
            'emp_correo'       => 'required|email|max:100',
        ], [
            'emp_razon_social.required' => 'La razón social es obligatoria.',
            'emp_direccion.required'    => 'La dirección es obligatoria.',
            'emp_telefono.required'     => 'El teléfono es obligatorio.',
            'emp_correo.required'       => 'El correo electrónico es obligatorio.',
            'emp_correo.email'          => 'El correo no tiene un formato válido.',
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
            'emp_razon_social' => 'required|string|max:200',
            'emp_direccion'    => 'required|string|max:300',
            'emp_telefono'     => 'required|string|max:30',
            'emp_correo'       => 'required|email|max:100',
        ], [
            'emp_razon_social.required' => 'La razón social es obligatoria.',
            'emp_direccion.required'    => 'La dirección es obligatoria.',
            'emp_telefono.required'     => 'El teléfono es obligatorio.',
            'emp_correo.required'       => 'El correo electrónico es obligatorio.',
            'emp_correo.email'          => 'El correo no tiene un formato válido.',
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

    public function destroy($id)
    {
        $empresa = Empresa::find($id);
        if (!$empresa) {
            return response()->json(['mensaje' => 'Empresa no encontrada', 'tipo' => 'error'], 404);
        }

        $tieneSucursales = DB::table('sucursal')->where('empresa_id', $id)->exists();
        if ($tieneSucursales) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la empresa porque tiene sucursales asociadas.',
                'tipo'    => 'error',
            ], 409);
        }

        try {
            $empresa->delete();
            return response()->json(['mensaje' => 'Empresa eliminada con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la empresa porque tiene registros asociados en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
