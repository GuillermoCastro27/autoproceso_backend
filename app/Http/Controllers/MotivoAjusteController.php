<?php

namespace App\Http\Controllers;

use App\Models\MotivoAjuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MotivoAjusteController extends Controller
{
    public function read()
    {
        return DB::select("SELECT id, descripcion, tipo_ajuste, created_at, updated_at FROM motivo_ajuste");
    }

    public function store(Request $r)
    {
        $r->validate([
            'descripcion' => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($r) {
                    $existe = \DB::table('motivo_ajuste')
                        ->whereRaw('LOWER(descripcion) = LOWER(?)', [trim($value)])
                        ->where('tipo_ajuste', $r->tipo_ajuste)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe un motivo con esa descripción para el mismo tipo de ajuste.');
                    }
                },
            ],
            'tipo_ajuste' => 'required|string|max:50',
        ], [
            'descripcion.required'  => 'La descripción del motivo es obligatoria.',
            'descripcion.max'       => 'La descripción no puede superar los 200 caracteres.',
            'descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
            'tipo_ajuste.required'  => 'El tipo de ajuste es obligatorio.',
        ]);

        $motivoajuste = MotivoAjuste::create([
            'descripcion' => $r->descripcion,
            'tipo_ajuste' => $r->tipo_ajuste,
        ]);

        return response()->json([
            'mensaje'  => 'Motivo de ajuste creado con éxito',
            'tipo'     => 'success',
            'registro' => $motivoajuste,
        ]);
    }

    public function update(Request $r, $id)
    {
        $motivoajuste = MotivoAjuste::find($id);
        if (!$motivoajuste) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'descripcion' => [
                'required', 'string', 'max:200', 'not_regex:/[*<>{}|]/',
                function ($attribute, $value, $fail) use ($r, $id) {
                    $existe = \DB::table('motivo_ajuste')
                        ->whereRaw('LOWER(descripcion) = LOWER(?)', [trim($value)])
                        ->where('tipo_ajuste', $r->tipo_ajuste)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existe) {
                        $fail('Ya existe otro motivo con esa descripción para el mismo tipo de ajuste.');
                    }
                },
            ],
            'tipo_ajuste' => 'required|string|max:50',
        ], [
            'descripcion.required'  => 'La descripción del motivo es obligatoria.',
            'descripcion.max'       => 'La descripción no puede superar los 200 caracteres.',
            'descripcion.not_regex' => 'La descripción contiene caracteres no permitidos.',
            'tipo_ajuste.required'  => 'El tipo de ajuste es obligatorio.',
        ]);

        $motivoajuste->update([
            'descripcion' => $r->descripcion,
            'tipo_ajuste' => $r->tipo_ajuste,
        ]);

        return response()->json([
            'mensaje'  => 'Motivo de ajuste actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $motivoajuste,
        ]);
    }

    public function cambiarEstado($id)
    {
        $motivoajuste = MotivoAjuste::find($id);
        if (!$motivoajuste) {
            return response()->json(['mensaje' => 'Motivo de Ajuste no encontrado', 'tipo' => 'error'], 404);
        }
        $nuevoEstado = strtolower($motivoajuste->estado ?? 'activo') === 'activo' ? 'inactivo' : 'activo';
        $motivoajuste->update(['estado' => $nuevoEstado]);
        $msg = $nuevoEstado === 'activo' ? 'Motivo de Ajuste activado con éxito.' : 'Motivo de Ajuste desactivado con éxito.';
        return response()->json(['mensaje' => $msg, 'tipo' => 'success', 'estado' => $nuevoEstado]);
    }
}
