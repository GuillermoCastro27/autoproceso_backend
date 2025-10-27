<?php

namespace App\Http\Controllers;

use App\Models\TipoPromociones;
use Illuminate\Http\Request;

class TipoPromocionesController extends Controller
{
    public function read()
    {
        return response()->json(
            TipoPromociones::select(
                'id as tipo_promociones_id',
                'tipo_prom_nombre',
                'tipo_prom_descrip',
                'tipo_prom_fechaInicio',
                'tipo_prom_fechaFin',
                'tipo_prom_modo',
                'tipo_prom_valor'
            )->get()
        );
    }

    public function store(Request $r)
    {
        $datosValidados = $r->validate([
            'tipo_prom_descrip' => 'required',
            'tipo_prom_nombre' => 'required',
            'tipo_prom_fechaInicio' => 'required',
            'tipo_prom_fechaFin' => 'required',
            'tipo_prom_modo' => 'required',
            'tipo_prom_valor' => 'required'
        ]);

        $tipopromociones = TipoPromociones::create($datosValidados);
        $tipopromociones->save();

        return response()->json([
            'mensaje' => 'Registro creado con exito',
            'tipo' => 'success',
            'registro' => $tipopromociones
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $tipopromociones = TipoPromociones::find($id);
        if (!$tipopromociones) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'tipo_prom_descrip' => 'required',
            'tipo_prom_nombre' => 'required',
            'tipo_prom_fechaInicio' => 'required',
            'tipo_prom_fechaFin' => 'required',
            'tipo_prom_modo' => 'required',
            'tipo_prom_valor' => 'required'
        ]);

        $tipopromociones->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con exito',
            'tipo' => 'success',
            'registro' => $tipopromociones
        ], 200);
    }

    public function destroy($id)
    {
        $tipopromociones = TipoPromociones::find($id);
        if (!$tipopromociones) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $tipopromociones->delete();

        return response()->json([
            'mensaje' => 'Registro Eliminado con exito',
            'tipo' => 'success',
        ], 200);
    }
}
