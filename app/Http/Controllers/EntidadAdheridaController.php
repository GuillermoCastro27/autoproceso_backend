<?php

namespace App\Http\Controllers;

use App\Models\EntidadAdherida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntidadAdheridaController extends Controller
{
    public function read()
    {
        return response()->json(
            DB::table('entidad_adherida as ea')
                ->join('entidad_emisora as ee', 'ee.id', '=', 'ea.entidad_emisora_id')
                ->join('marca_tarjeta as mt', 'mt.id', '=', 'ea.marca_tarjeta_id')
                ->select(
                    'ea.id as entidad_adherida_id',

                    'ea.entidad_emisora_id',
                    'ee.ent_emis_nombre as entidad_emisora',

                    'ea.marca_tarjeta_id',
                    'mt.marca_nombre as marca_tarjeta',

                    'ea.ent_adh_nombre',
                    'ea.ent_adh_direccion',
                    'ea.ent_adh_telefono',
                    'ea.ent_adh_email'
                )
                ->orderBy('ea.id')
                ->get()
        );
    }
    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'entidad_emisora_id' => 'required|exists:entidad_emisora,id',
            'marca_tarjeta_id'   => 'required|exists:marca_tarjeta,id',
            'ent_adh_nombre'     => 'required|string|max:150',
            'ent_adh_direccion'  => 'nullable|string|max:200',
            'ent_adh_telefono'   => 'nullable|string|max:50',
            'ent_adh_email'      => 'nullable|email|max:100'
        ], [
            'entidad_emisora_id.required' => 'Debe seleccionar una entidad emisora.',
            'marca_tarjeta_id.required'   => 'Debe seleccionar una marca de tarjeta.',
            'ent_adh_nombre.required'     => 'El nombre de la entidad adherida es obligatorio.',
            'ent_adh_email.email'         => 'El formato del correo no es válido.'
        ]);

        $entidadAdherida = EntidadAdherida::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Entidad adherida registrada con éxito',
            'tipo'     => 'success',
            'registro' => $entidadAdherida
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $entidadAdherida = EntidadAdherida::find($id);

        if (!$entidadAdherida) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'entidad_emisora_id' => 'required|exists:entidad_emisora,id',
            'marca_tarjeta_id'   => 'required|exists:marca_tarjeta,id',
            'ent_adh_nombre'     => 'required|string|max:150',
            'ent_adh_direccion'  => 'nullable|string|max:200',
            'ent_adh_telefono'   => 'nullable|string|max:50',
            'ent_adh_email'      => 'nullable|email|max:100'
        ]);

        $entidadAdherida->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Entidad adherida modificada con éxito',
            'tipo'     => 'success',
            'registro' => $entidadAdherida
        ], 200);
    }

    public function destroy($id)
    {
        $entidadAdherida = EntidadAdherida::find($id);

        if (!$entidadAdherida) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $entidadAdherida->delete();

        return response()->json([
            'mensaje' => 'Entidad adherida eliminada con éxito',
            'tipo'    => 'success'
        ], 200);
    }
}
