<?php

namespace App\Http\Controllers;

use App\Models\CompraCab;
use App\Models\OrdenCompraCab; 
use App\Models\CompraDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraCabController extends Controller
{
    public function read() {
        return DB::select("
            SELECT 
            c.*,
            COALESCE(to_char(c.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS comp_intervalo_fecha_vence,
            c.comp_fecha,
            c.comp_estado,
            COALESCE(c.comp_cant_cuota::varchar, '0') AS comp_cant_cuota,
            c.condicion_pago,
            p.prov_razonsocial,
            p.prov_ruc,
            p.prov_telefono,
            p.prov_correo,
            e.emp_razon_social,
            s.suc_razon_social,
            u.name AS encargado,
            COALESCE('ORDEN DE COMPRA NRO: ' || to_char(occ.id, '0000000'), 'SIN ORDEN DE COMPRA') || 
            COALESCE(' VENCE EL: ' || to_char(occ.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS ordencompra
        FROM 
            compra_cab c
        JOIN 
            proveedores p ON p.id = c.proveedor_id
        JOIN 
            empresa e ON e.id = c.empresa_id
        JOIN 
            sucursal s ON s.empresa_id = c.sucursal_id
        JOIN 
            users u ON u.id = c.user_id
        LEFT JOIN 
            orden_compra_cab occ ON occ.id = c.orden_compra_cab_id;
        ");
    }    
    public function store(Request $r) {
        // Convertir cadena vacía a null antes de la validación
        if ($r->comp_intervalo_fecha_vence === '') {
            $r->merge(['comp_intervalo_fecha_vence' => null]);
        }
    
        // Establecer comp_cant_cuota como null si la condición de pago es "CONTADO"
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
        }
    
        // Validación de datos
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha' => 'nullable|date',
            'comp_estado' => 'required',
            'comp_cant_cuota' => 'nullable|integer',
            'condicion_pago' => 'required',
            'user_id' => 'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
    
        // Crear la cabecera de la compra
        $compracab = CompraCab::create($datosValidados);
    
        // Obtener la orden de compra cabecera
        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        if ($ordencompracab) {
            $ordencompracab->ord_comp_estado = "PROCESADO"; // Cambiar el estado de la orden
            $ordencompracab->save();
    
            // Obtener los detalles de la orden de compra
            $detalles = DB::table('orden_compra_det')
                ->where('orden_compra_cab_id', $ordencompracab->id)
                ->get();
    
            // Insertar los detalles en la tabla compra_det
            foreach ($detalles as $detalle) {
                CompraDet::create([
                    'compra_cab_id' => $compracab->id,
                    'item_id' => $detalle->item_id,
                    'comp_det_cantidad' => $detalle->orden_compra_det_cantidad,
                    'comp_det_costo' => $detalle->orden_compra_det_costo,
                    'tipo_impuesto_id' => $detalle->tipo_impuesto_id,
                ]);
            }
        }
    
        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $compracab
        ], 201);
    }

    public function update(Request $r, $id){
        $compracab = CompraCab::find($id);
        // Convertir cadena vacía a null antes de la validación
    if ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Establecer   _comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        // Asegurar que estos campos sean null para pagos al contado
        $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
    }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'nullable|date',
            'comp_fecha'=>'nullable|date',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'nullable|integer',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function anular(Request $r, $id) {
        // Buscar el registro por el ID
        $compracab = CompraCab::find($id);
        
        if ($r->comp_intervalo_fecha_vence === '') {
            $r->merge(['comp_intervalo_fecha_vence' => null]);
        }
    
        // Establecer   _comp_cant_cuota como null si la condición de pago es "CONTADO"
        if ($r->condicion_pago === 'CONTADO') {
            // Asegurar que estos campos sean null para pagos al contado
            $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
        }
    
        // Validar los datos de la solicitud
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha' => 'nullable|date',
            'comp_estado' => 'required',
            'comp_cant_cuota' => 'nullable|integer',
            'condicion_pago' => 'required',
            'user_id' => 'required',
            'orden_compra_cab_id' => 'required',
            'proveedor_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);
        
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
    
        // Actualizar los datos validados
        $compracab->update($datosValidados);
    
        // Cambiar el estado a "CONFIRMADO"
        $compracab->comp_estado = "ANULADO";
        $compracab->save();
        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id); // Cambiado a presupuestos_id

        // Verificar si el presupuesto existe
        if (!$ordencompracab) {
            return response()->json([
                'mensaje' => 'Presupuesto no encontrado',
                'tipo' => 'error',
            ], 404);
        }
    
        // Devolver la respuesta exitosa
        return response()->json([
            'mensaje' => 'Registro anulado con éxito',
            'tipo' => 'success',
            'registro' => $compracab
        ], 200);
    }
    public function confirmar(Request $r, $id){
        $compracab = CompraCab::find($id);
        if ($r->comp_intervalo_fecha_vence === '') {
            $r->merge(['comp_intervalo_fecha_vence' => null]);
        }
    
        // Establecer   _comp_cant_cuota como null si la condición de pago es "CONTADO"
        if ($r->condicion_pago === 'CONTADO') {
            // Asegurar que estos campos sean null para pagos al contado
            $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
        }
            $datosValidados = $r->validate([
                'comp_intervalo_fecha_vence'=>'nullable|date',
                'comp_fecha'=>'nullable|date',
                'comp_estado'=>'required',
                'comp_cant_cuota'=>'nullable|integer',
                'condicion_pago'=>'required',
                'user_id'=>'required',
                'orden_compra_cab_id'=>'required',
                'proveedor_id'=>'required',
                'empresa_id'=>'required',
                'sucursal_id'=>'required'
            ]);
            if ($r->condicion_pago === 'CONTADO') {
                $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
                $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
            }
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }
}