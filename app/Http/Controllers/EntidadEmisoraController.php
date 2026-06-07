<?php

namespace App\Http\Controllers;

use App\Models\EntidadEmisora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntidadEmisoraController extends Controller
{
    public function read()
    {
        return response()->json(
            EntidadEmisora::select(
                'id as entidad_emisora_id',
                'ent_emis_nombre as ent_emis_nombre',
                'ent_emis_direccion as ent_emis_direccion',
                'ent_emis_telefono as ent_emis_telefono',
                'ent_emis_email as ent_emis_email',
                'ent_emis_estado as ent_emis_estado'
                )->get()
        );
    }
    public function store(Request $r)
    {
        // ✅ Validación
        $datosValidados = $r->validate([
            'ent_emis_nombre'    => [
                'required', 'string', 'max:150', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) {
                    $existe = \DB::table('entidad_emisora')
                        ->whereRaw('LOWER(ent_emis_nombre) = LOWER(?)', [trim($value)])
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe una entidad emisora con ese nombre.');
                    }
                },
            ],
            'ent_emis_direccion' => 'nullable|string|max:200',
            'ent_emis_telefono'  => 'nullable|string|max:50',
            'ent_emis_email'     => 'nullable|email|max:100',
            'ent_emis_estado'    => 'required|string|max:20'
        ], [
            'ent_emis_nombre.required' => 'El nombre de la entidad emisora es obligatorio.',
            'ent_emis_email.email'     => 'El formato del correo electrónico no es válido.',
            'ent_emis_estado.required' => 'El estado es obligatorio.'
        ]);

        // ✅ Crear registro
        $entidadEmisora = EntidadEmisora::create($datosValidados);

        // ✅ Respuesta
        return response()->json([
            'mensaje'  => 'Entidad emisora creada con éxito',
            'tipo'     => 'success',
            'registro' => $entidadEmisora
        ], 200);
    }
    public function update(Request $r, $id)
{
    $entidadEmisora = EntidadEmisora::find($id);

    if (!$entidadEmisora) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    // ✅ Validación (ignora el mismo registro en unique)
    $datosValidados = $r->validate([
        'ent_emis_nombre'    => [
            'required', 'string', 'max:150', 'not_regex:/[*<>{}|]/',
            function ($attribute, $value, $fail) use ($id) {
                $existe = \DB::table('entidad_emisora')
                    ->whereRaw('LOWER(ent_emis_nombre) = LOWER(?)', [trim($value)])
                    ->where('id', '!=', $id)
                    ->exists();
                if ($existe) {
                    $fail('Ya existe otra entidad emisora con ese nombre.');
                }
            },
        ],
        'ent_emis_direccion' => 'nullable|string|max:200',
        'ent_emis_telefono'  => 'nullable|string|max:50',
        'ent_emis_email'     => 'nullable|email|max:100',
        'ent_emis_estado'    => 'required|string|max:20'
    ], [
        'ent_emis_nombre.required' => 'El nombre de la entidad emisora es obligatorio.',
        'ent_emis_email.email'     => 'El formato del correo electrónico no es válido.',
        'ent_emis_estado.required' => 'El estado es obligatorio.'
    ]);

    // ✅ Actualizar
    $entidadEmisora->update($datosValidados);

    return response()->json([
        'mensaje'  => 'Registro modificado con éxito',
        'tipo'     => 'success',
        'registro' => $entidadEmisora
    ], 200);
}
public function cambiarEstado($id)
{
    $entidadEmisora = EntidadEmisora::find($id);
    if (!$entidadEmisora) {
        return response()->json(['mensaje' => 'Entidad Emisora no encontrada', 'tipo' => 'error'], 404);
    }
    $nuevoEstado = strtolower($entidadEmisora->ent_emis_estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
    $entidadEmisora->update(['ent_emis_estado' => $nuevoEstado]);
    $msg = $nuevoEstado === 'activo' ? 'Entidad Emisora activada con éxito.' : 'Entidad Emisora desactivada con éxito.';
    return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
}
public function buscarEntidadEmisora()
{
    return response()->json(
        EntidadEmisora::select(
            'id as entidad_emisora_id',
            'ent_emis_nombre',
            'ent_emis_direccion',
            'ent_emis_telefono',
            'ent_emis_email'
        )
        ->whereRaw("LOWER(ent_emis_estado) = 'activo'")
        ->orderBy('ent_emis_nombre')
        ->get()
    );
}


}
