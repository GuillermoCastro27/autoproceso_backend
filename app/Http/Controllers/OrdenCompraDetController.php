<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraDetController extends Controller
{
    public function read($id) {
        return DB::select("
            select 
                ocd.*, 
                i.item_decripcion, 
                ti.tip_imp_nom
            from orden_compra_det ocd
            join items i on i.id = ocd.item_id
            join tipo_impuesto ti on ti.id = ocd.tipo_impuesto_id
            where ocd.orden_compra_cab_id = $id");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            "orden_compra_cab_id"=>"required",
            "item_id"=>"required",
            "tipo_impuesto_id"=>"required",
            "orden_compra_det_cantidad"=>"required"
        ]);
        $detalle = OrdenCompraDet::create($datosValidados);
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
    public function update(Request $r, $orden_compra_cab_id, $item_id)
{
    // Validar los datos del request y asignarlos a $datosValidados
    $datosValidados = $r->validate([
        "orden_compra_det_cantidad" => "required|numeric",
        "tipo_impuesto_id" => "required|exists:tipo_impuesto,id"
    ]);

    // Actualizar el registro en la tabla orden_compra_det
    $ordencompradet = DB::table('orden_compra_det')
        ->where('orden_compra_cab_id', $orden_compra_cab_id)
        ->where('item_id', $item_id)
        ->update([
            'orden_compra_det_cantidad' => $datosValidados['orden_compra_det_cantidad'],
            'tipo_impuesto_id' => $datosValidados['tipo_impuesto_id']
        ]);

    // Obtener el registro actualizado para retornar en la respuesta
    $ordencompradet = DB::select("select * from orden_compra_det where orden_compra_cab_id = ? and item_id = ?", [$orden_compra_cab_id, $item_id]);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $ordencompradet
    ], 200);
}
    
    public function destroy($orden_compra_cab_id, $item_id){
        $detalle = DB::table('orden_compra_det')->
        where('orden_compra_cab_id', $orden_compra_cab_id)->
        where('item_id', $item_id)->
        delete();

        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
            'registro'=> $detalle
        ],200);
    }
}
