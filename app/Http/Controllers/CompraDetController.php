<?php

namespace App\Http\Controllers;

use App\Models\CompraDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CompraDetController extends Controller
{
    public function read($id) {
        return DB::select("
            select 
                cd.*, 
                i.item_decripcion, 
                i.item_costo,  
                ti.tip_imp_nom
            from compra_det cd
            join items i on i.id = cd.item_id
            join tipo_impuesto ti on ti.id = cd.tipo_impuesto_id
            where cd.compra_cab_id = $id");
    }
    public function store(Request $request)
{
    $datosValidados = $request->validate([
        'compra_cab_id' => 'required|exists:compra_cab,id',
        'item_id' => 'required|exists:items,id',
        'tipo_impuesto_id' => 'required|exists:tipo_impuesto,id',
        'comp_det_cantidad' => 'required|numeric',
        'comp_det_costo' => 'required|numeric', // Validar el costo
    ]);

    // Crear el detalle de la orden de compra
    $detalle = CompraDet::create($datosValidados);
    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $detalle
    ], 201);
}

public function update(Request $r, $compra_cab_id, $item_id)
{
    // Validar los datos del request y asignarlos a $datosValidados
    $datosValidados = $r->validate([
        "comp_det_cantidad" => "required|numeric",
        "tipo_impuesto_id" => "required|exists:tipo_impuesto,id",
        "comp_det_costo" => "required|numeric", // Validar el costo
    ]);

    // Actualizar el registro en la tabla orden_compra_det
    $compradet = DB::table('compra_det')
        ->where('compra_cab_id', $compra_cab_id)
        ->where('item_id', $item_id)
        ->update([
            'comp_det_cantidad' => $datosValidados['comp_det_cantidad'],
            'tipo_impuesto_id' => $datosValidados['tipo_impuesto_id'],
            'comp_det_costo' => $datosValidados['comp_det_costo'], // Asegúrate de incluir el costo aquí
        ]);

    // Verificar si la actualización fue exitosa
    if ($compradet) {
        // Obtener el registro actualizado para retornar en la respuesta
        $compradet = DB::select("select * from compra_det where compra_cab_id = ? and item_id = ?", [$compra_cab_id, $item_id]);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $compradet
        ], 200);
    } else {
        return response()->json([
            'mensaje' => 'Error al modificar el registro',
            'tipo' => 'error'
        ], 500);
    }
}
    
    public function destroy($compra_cab_id, $item_id){
        $detalle = DB::table('compra_det')->
        where('compra_cab_id', $compra_cab_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
