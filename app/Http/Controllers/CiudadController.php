<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CiudadController extends Controller
{
    public function read()
    {
        return DB::select('SELECT c.*, p.pais_descrpcion FROM ciudades c INNER JOIN paises p ON p.id = c.pais_id');
    }

    public function store(Request $r)
    {
        $r->validate([
            'ciu_descripcion' => [
                'required', 'string', 'max:200',
                Rule::unique('ciudades', 'ciu_descripcion')->where(fn($q) => $q->where('pais_id', $r->pais_id)),
            ],
            'pais_id' => 'required|integer|exists:paises,id',
        ], [
            'ciu_descripcion.required' => 'El nombre de la ciudad es obligatorio.',
            'ciu_descripcion.max'      => 'El nombre no puede superar los 200 caracteres.',
            'ciu_descripcion.unique'   => 'Ya existe una ciudad con ese nombre en el país seleccionado.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'pais_id.exists'           => 'El país seleccionado no existe.',
        ]);

        $ciudad = Ciudad::create([
            'ciu_descripcion' => $r->ciu_descripcion,
            'pais_id'         => $r->pais_id,
        ]);

        return response()->json([
            'mensaje'  => 'Ciudad creada con éxito',
            'tipo'     => 'success',
            'registro' => $ciudad,
        ]);
    }

    public function update(Request $r, $id)
    {
        $ciudad = Ciudad::find($id);
        if (!$ciudad) {
            return response()->json(['mensaje' => 'Ciudad no encontrada', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'ciu_descripcion' => [
                'required', 'string', 'max:200',
                Rule::unique('ciudades', 'ciu_descripcion')
                    ->where(fn($q) => $q->where('pais_id', $r->pais_id))
                    ->ignore($id),
            ],
            'pais_id' => 'required|integer|exists:paises,id',
        ], [
            'ciu_descripcion.required' => 'El nombre de la ciudad es obligatorio.',
            'ciu_descripcion.max'      => 'El nombre no puede superar los 200 caracteres.',
            'ciu_descripcion.unique'   => 'Ya existe otra ciudad con ese nombre en el país seleccionado.',
            'pais_id.required'         => 'Debe seleccionar un país.',
            'pais_id.exists'           => 'El país seleccionado no existe.',
        ]);

        $ciudad->update([
            'ciu_descripcion' => $r->ciu_descripcion,
            'pais_id'         => $r->pais_id,
        ]);

        return response()->json([
            'mensaje'  => 'Ciudad actualizada con éxito',
            'tipo'     => 'success',
            'registro' => $ciudad,
        ]);
    }

    public function destroy($id)
    {
        $ciudad = Ciudad::find($id);
        if (!$ciudad) {
            return response()->json(['mensaje' => 'Ciudad no encontrada', 'tipo' => 'error'], 404);
        }

        try {
            $ciudad->delete();
            return response()->json(['mensaje' => 'Ciudad eliminada con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'No se puede eliminar la ciudad porque está siendo utilizada en el sistema.',
                'tipo'    => 'error',
            ], 409);
        }
    }
}
