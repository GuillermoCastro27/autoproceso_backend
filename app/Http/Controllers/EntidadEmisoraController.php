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
            'ent_emis_nombre'    => 'required|string|max:150|unique:entidad_emisora,ent_emis_nombre',
            'ent_emis_direccion' => 'nullable|string|max:200',
            'ent_emis_telefono'  => 'nullable|string|max:50',
            'ent_emis_email'     => 'nullable|email|max:100',
            'ent_emis_estado'    => 'required|string|max:20'
        ], [
            'ent_emis_nombre.required' => 'El nombre de la entidad emisora es obligatorio.',
            'ent_emis_nombre.unique'   => 'La entidad emisora ya existe.',
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
        'ent_emis_nombre'    => 'required|string|max:150|unique:entidad_emisora,ent_emis_nombre,' . $id,
        'ent_emis_direccion' => 'nullable|string|max:200',
        'ent_emis_telefono'  => 'nullable|string|max:50',
        'ent_emis_email'     => 'nullable|email|max:100',
        'ent_emis_estado'    => 'required|string|max:20'
    ], [
        'ent_emis_nombre.required' => 'El nombre de la entidad emisora es obligatorio.',
        'ent_emis_nombre.unique'   => 'La entidad emisora ya existe.',
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
public function destroy($id)
{
    $entidadEmisora = EntidadEmisora::find($id);

    if (!$entidadEmisora) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    $entidadEmisora->update([
        'ent_emis_estado' => 'INACTIVO'
    ]);

    return response()->json([
        'mensaje' => 'Entidad emisora anulada con éxito',
        'tipo'    => 'success'
    ], 200);
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
        ->where('ent_emis_estado', 'ACTIVO')
        ->orderBy('ent_emis_nombre')
        ->get()
    );
}


}
