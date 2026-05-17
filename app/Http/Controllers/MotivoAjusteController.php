<?php

namespace App\Http\Controllers;

use App\Models\MotivoAjuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
                'required', 'string', 'max:200',
                Rule::unique('motivo_ajuste', 'descripcion')->where(fn($q) => $q->where('tipo_ajuste', $r->tipo_ajuste)),
            ],
            'tipo_ajuste' => 'required|string|max:50',
        ], [
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 200 caracteres.',
            'descripcion.unique'   => 'Ya existe un motivo con esa descripción para el mismo tipo de ajuste.',
            'tipo_ajuste.required' => 'El tipo de ajuste es obligatorio.',
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
                'required', 'string', 'max:200',
                Rule::unique('motivo_ajuste', 'descripcion')
                    ->where(fn($q) => $q->where('tipo_ajuste', $r->tipo_ajuste))
                    ->ignore($id),
            ],
            'tipo_ajuste' => 'required|string|max:50',
        ], [
            'descripcion.required' => 'La descripción del motivo es obligatoria.',
            'descripcion.max'      => 'La descripción no puede superar los 200 caracteres.',
            'descripcion.unique'   => 'Ya existe otro motivo con esa descripción para el mismo tipo de ajuste.',
            'tipo_ajuste.required' => 'El tipo de ajuste es obligatorio.',
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

    public function destroy($id)
    {
        $motivoajuste = MotivoAjuste::find($id);
        if (!$motivoajuste) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $motivoajuste->delete();
            return response()->json(['mensaje' => 'Motivo de ajuste eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar el motivo porque está siendo utilizado en ajustes de inventario.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
