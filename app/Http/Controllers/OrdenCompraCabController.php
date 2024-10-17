<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraCab;
use App\Models\Presupuesto; 
use App\Models\OrdenCompraDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            o.id,
            COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
            o.ord_comp_fecha,
            o.ord_comp_estado,
            COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota, -- Cambiado a varchar
            o.condicion_pago,
            p.id AS proveedor_id,
            p.prov_razonsocial AS prov_razonsocial,
            p.prov_ruc AS prov_ruc,
            p.prov_telefono AS prov_telefono,
            p.prov_correo AS prov_correo,
            o.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            o.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            pr.id AS presupuesto_id,
            'PRESUPUESTO NRO: ' || to_char(pr.id, '0000000') || ' VENCE EL: ' || COALESCE(to_char(pr.pre_vence, 'dd/mm/yyyy HH24:mi:ss'), 'N/A') || ' (' || pr.pre_observaciones || ')' AS presupuesto,
            u.name AS encargado  
        FROM 
            orden_compra_cab o
        JOIN 
            users u ON u.id = o.user_id
        JOIN 
            sucursal s ON s.empresa_id = o.sucursal_id
        JOIN 
            empresa e ON e.id = o.empresa_id
        JOIN 
            presupuestos pr ON pr.id = o.presupuesto_id
        JOIN 
            proveedores p ON p.id = pr.proveedor_id
    ");
}
public function store(Request $r) {
    // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }

    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date',
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer', // Cambiado a presupuestos_id
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
        $datosValidados['ord_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
    }

    $ordencompracab = OrdenCompraCab::create($datosValidados);
    $ordencompracab->save();

    // Buscar el presupuesto por su ID
    $presupuesto = Presupuesto::find($r->presupuesto_id); // Cambiado a presupuestos_id

    // Verificar si el presupuesto existe
    if (!$presupuesto) {
        return response()->json([
            'mensaje' => 'Presupuesto no encontrado',
            'tipo' => 'error',
        ], 404);
    }

    // Actualizar el estado del presupuesto a "PROCESADO"
    $presupuesto->pre_estado = "PROCESADO"; // Cambiado a pre_estado
    $presupuesto->save();

    // Lógica para guardar detalles
    $detalles = DB::select("SELECT 
    pd.*, 
    i.item_decripcion,
    pd.det_costo as orden_compra_det_costo,
    pd.det_cantidad as orden_compra_det_cantidad,
    i.tipo_impuesto_id
    FROM presupuestos_detalles pd 
    JOIN items i ON i.id = pd.item_id 
    WHERE pd.presupuesto_id = $presupuesto->id;");

    foreach ($detalles as $ocd) {
        $ordencompradet = new OrdenCompraDet();
        $ordencompradet->orden_compra_cab_id = $ordencompracab->id;
        $ordencompradet->item_id = $ocd->item_id;
        $ordencompradet->orden_compra_det_costo = $ocd->orden_compra_det_costo;
        $ordencompradet->orden_compra_det_cantidad = $ocd->orden_compra_det_cantidad;
        $ordencompradet->tipo_impuesto_id = $ocd->tipo_impuesto_id; 
        $ordencompradet->save();
    }

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}

    public function update(Request $r, $id)
{
    $ordencompracab = OrdenCompraCab::find($id);
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }
     // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }

    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date',
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer', // Cambiado a presupuestos_id
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
        $datosValidados['ord_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
    }

    $ordencompracab->update($datosValidados);
    
    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}
public function anular(Request $r, $id){
    $ordencompracab = OrdenCompraCab::find($id);
    if(!$ordencompracab){
        return response()->json([
            'mensaje'=>'Registro no encontrado',
            'tipo'=>'error'
        ],404);
    }
    // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }

    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date',
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer', // Cambiado a presupuestos_id
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
        $datosValidados['ord_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
    }

    $ordencompracab->update($datosValidados);
    return response()->json([
        'mensaje'=>'Registro anulado con exito',
        'tipo'=>'success',
        'registro'=> $ordencompracab
    ],200);
}
public function confirmar(Request $r, $id) {
    $ordencompracab = OrdenCompraCab::find($id);
    
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }
    // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }

    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date',
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer', // Cambiado a presupuestos_id
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
        $datosValidados['ord_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
    }

    $ordencompracab->update($datosValidados);
    $ordencompracab->save();

    return response()->json([
        'mensaje' => 'Orden confirmada y detalle guardado con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}
public function buscar(Request $r,)
{
    $userId = $r->input('user_id'); // Obtener el valor desde la request
    $userName = $r->input('name');  // Obtener el valor del nombre

    return DB::select("
        SELECT 
            o.id AS orden_compra_cab_id,
            TO_CHAR(o.ord_comp_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ord_comp_fecha,
            COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
            o.ord_comp_estado,
            o.condicion_pago,
            COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota,
            o.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            o.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            o.user_id,
            o.created_at,
            o.updated_at,
            u.name, 
            u.email,
            o.proveedor_id,
            prov.prov_razonsocial,
            prov.prov_ruc,
            prov.prov_telefono,
            prov.prov_correo,
            'ORDEN COMPRA NRO: ' || TO_CHAR(o.id, '0000000') || ' VENCE EL: ' || TO_CHAR(o.ord_comp_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ordencompra,
            COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss'), 'N/A') as comp_intervalo_fecha_vence,
            COALESCE(o.ord_comp_cant_cuota::varchar, '0') as comp_cantidad_cuota
        FROM 
            orden_compra_cab o
        JOIN 
            users u ON u.id = o.user_id
        JOIN 
            sucursal s ON s.empresa_id = o.sucursal_id
        JOIN 
            empresa e ON e.id = o.empresa_id
        JOIN 
            proveedores prov ON prov.id = o.proveedor_id
        WHERE 
            o.ord_comp_estado = 'CONFIRMADO'
        AND 
            o.user_id = ?
        AND 
            u.name ILIKE ?
    ", [$userId, '%' . $userName . '%']); // Utilizar bindings para evitar SQL Injection
}
}
