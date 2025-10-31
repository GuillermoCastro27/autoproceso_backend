<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function read(){
        return DB::table('items')
        ->join('marca', 'items.marca_id', '=', 'marca.id')
        ->join('modelo', 'items.modelo_id', '=', 'modelo.id')
        ->join('tipos', 'items.tipo_id', '=', 'tipos.id')
        ->join('tipo_impuesto', 'items.tipo_impuesto_id', '=', 'tipo_impuesto.id')
        ->select('items.*','marca.marc_nom as marc_nom','modelo.modelo_nom as modelo_nom','tipos.tipo_descripcion as tipo_descripcion','tipo_impuesto.tip_imp_nom as tip_imp_nom')
        ->get();
    }

    public function store(Request $r){
        // Mensajes personalizados para la validación
        $messages = [
            'item_costo.min' => 'El costo no puede ser negativo.',
            'item_precio.min' => 'El precio no puede ser negativo.',
        ];
    
        try {
            // Validación de los datos
            $datosValidados = $r->validate([
                'item_decripcion' => 'required',
                'item_costo' => 'required|numeric|min:0',
                'item_precio' => 'required|numeric|min:0',
                'tipo_id' => 'required',
                'tipo_impuesto_id' => 'required',
                'marca_id' => 'required',
                'modelo_id' => 'required'
            ], $messages);
    
            // Creación del item
            $item = Item::create($datosValidados);
            $item->save();
    
            // Respuesta de éxito
            return response()->json([
                'mensaje' => 'Registro creado con éxito',
                'tipo' => 'success',
                'registro' => $item
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura de errores de validación y respuesta
            return response()->json([
                'status' => 'error',
                'message' => 'Existen errores en los datos enviados.',
                'errors' => $e->errors()
            ], 422);
        }
    }    
    public function update(Request $r, $id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }
    
        // Mensajes personalizados para la validación
        $messages = [
            'item_costo.min' => 'El costo no puede ser negativo.',
            'item_precio.min' => 'El precio no puede ser negativo.',
        ];
    
        try {
            // Validación de los datos
            $datosValidados = $r->validate([
                'item_decripcion' => 'required',
                'item_costo' => 'required|numeric|min:0',
                'item_precio' => 'required|numeric|min:0',
                'tipo_id' => 'required',
                'tipo_impuesto_id' => 'required',
                'marca_id' => 'required',
                'modelo_id' => 'required'
            ], $messages);
    
            // Actualización del item
            $item->update($datosValidados);
    
            // Respuesta de éxito
            return response()->json([
                'mensaje' => 'Registro modificado con éxito',
                'tipo' => 'success',
                'registro' => $item
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura de errores de validación y respuesta
            return response()->json([
                'status' => 'error',
                'message' => 'Existen errores en los datos enviados.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy($id){
        $item = Item::find($id);
        if(!$item){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $item->delete();
        return response()->json([
            'mensaje'=>'Registro Eliminado con exito',
            'tipo'=>'success',
        ],200);
    }

    public function buscar(Request $r) {
        $productos = DB::select("
            SELECT 
                i.*, 
                ti.tip_imp_nom, 
                ti.tipo_imp_tasa, 
                i.item_costo, 
                i.id as item_id,
                COALESCE(SUM(s.cantidad), 0) AS cantidad_disponible
            FROM items i
            JOIN tipos t ON t.id = i.tipo_id
            LEFT JOIN tipo_impuesto ti ON ti.id = i.tipo_impuesto_id
            LEFT JOIN stock s ON s.item_id = i.id
            WHERE i.item_decripcion ILIKE '%$r->item_decripcion%'
            GROUP BY i.id, ti.tip_imp_nom, ti.tipo_imp_tasa, t.tipo_descripcion
        ");
        
        return response()->json($productos);
    }      
       
}
