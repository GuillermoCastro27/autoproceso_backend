<?php

namespace App\Http\Controllers;
use App\Models\TipoDiagnostico;

use Illuminate\Http\Request;

class TipoDiagnosticoController extends Controller
{
     public function read()
    {
        return response()->json(
            TipoDiagnostico::select('id as tipo_diagnostico_id', 'tipo_diag_nombre as tipo_diag_nombre', 'tipo_diag_descrip as tipo_diag_descrip')->get()
        );
    }

    public function store(Request $r){
        $datosValidados = $r->validate([
            'tipo_diag_nombre' => 'required|string|max:100|unique:tipo_diagnostico,tipo_diag_nombre',
            'tipo_diag_descrip' => 'required|string|max:255',
        ], [
            'tipo_diag_nombre.required' => 'El campo nombre es obligatorio.',
            'tipo_diag_nombre.unique' => 'El tipo de diagnóstico ya existe.',
            'tipo_diag_descrip.required' => 'El campo descripción es obligatorio.',
            'tipo_diag_descrip.unique' => 'El campo descripción ya existe.',
        ]);
        $datosValidados = $r->validate([
            'tipo_diag_nombre'=>'required',
            'tipo_diag_descrip'=>'required'
        ]);
        $tipodiagnostico = TipoDiagnostico::create($datosValidados);
        $tipodiagnostico->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $tipodiagnostico
        ],200);
    }

    public function update(Request $r, $id)
    {
        $tipoContrato = TipoContrato::find($id);

        if (!$tipoContrato) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $datosValidados = $r->validate([
            'tip_con_nombre' => 'required|string|max:100|unique:tipo_contrato,tip_con_nombre,' . $id,

            'tip_con_objeto' => 'required|string',
            'tip_con_alcance' => 'required|string',
            'tip_con_garantia' => 'required|string',
            'tip_con_responsabilidad' => 'required|string',
            'tip_con_limitacion' => 'required|string',
            'tip_con_fuerza_mayor' => 'required|string',
            'tip_con_jurisdiccion' => 'required|string',

            'tip_con_estado' => 'nullable|string|max:20'
        ], [
            'tip_con_nombre.required' => 'El nombre del tipo de contrato es obligatorio.',
            'tip_con_nombre.unique' => 'El tipo de contrato ya existe.',

            'tip_con_objeto.required' => 'El objeto del contrato es obligatorio.',
            'tip_con_alcance.required' => 'El alcance del contrato es obligatorio.',
            'tip_con_garantia.required' => 'La garantía es obligatoria.',
            'tip_con_responsabilidad.required' => 'La responsabilidad es obligatoria.',
            'tip_con_limitacion.required' => 'La limitación es obligatoria.',
            'tip_con_fuerza_mayor.required' => 'La cláusula de fuerza mayor es obligatoria.',
            'tip_con_jurisdiccion.required' => 'La jurisdicción es obligatoria.',
        ]);

        $tipoContrato->update($datosValidados);

        return response()->json([
            'mensaje' => 'Tipo de contrato modificado con éxito',
            'tipo' => 'success',
            'registro' => $tipoContrato
        ], 200);
    }

    public function destroy($id)
    {
        $tipoContrato = TipoContrato::find($id);

        if (!$tipoContrato) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        $tipoContrato->update([
            'tip_con_estado' => 'INACTIVO'
        ]);

        return response()->json([
            'mensaje' => 'Tipo de contrato inactivado con éxito',
            'tipo' => 'success'
        ], 200);
    }

}
