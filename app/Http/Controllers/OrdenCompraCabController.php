<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompraCab;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class OrdenCompraCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            o.id,
            COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
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
    public function store(Request $r){
        // Convertir cadena vacía a null antes de la validación
    if ($r->ord_comp_intervalo_fecha_vence === '') {
        $r->merge(['ord_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO" antes de la validación
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }
        $datosValidados = $r->validate([
            'ord_comp_intervalo_fecha_vence' => 'nullable|date', // Cambia a nullable si es opcional
            'ord_comp_fecha' => 'required|date',
            'ord_comp_estado' => 'required',
            'ord_comp_cant_cuota' => 'nullable|integer',
            'user_id' => 'required|integer',
            'presupuesto_id' => 'required|integer',
            'proveedor_id' => 'required|integer',
            'empresa_id' => 'required|integer',
            'sucursal_id' => 'required|integer',
            'condicion_pago' => 'required|string|max:20'
        ]);
    
        // Aquí puedes agregar la lógica adicional para manejar los campos en función de la condición de pago
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['ord_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['ord_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
    
        $ordencompracab = OrdenCompraCab::create($datosValidados);
        
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

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO" antes de la validación
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge(['ord_comp_cant_cuota' => null]); // Establece null para cuotas en "CONTADO"
    }

    // Validación condicional según la condición de pago
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date', // Cambia a nullable si es opcional
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer',
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

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
    // Si la condición de pago es 'CONTADO', asignar valores específicos antes de la validación
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'ord_comp_intervalo_fecha_vence' => null,
            'ord_comp_cant_cuota' => null
        ]);
    }
    $datosValidados = $r->validate([
        'ord_comp_intervalo_fecha_vence' => 'nullable|date', // Cambia a nullable si es opcional
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'ord_comp_cant_cuota' => 'nullable|integer',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer',
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);
    $ordencompracab->update($datosValidados);
    return response()->json([
        'mensaje'=>'Registro anulado con exito',
        'tipo'=>'success',
        'registro'=> $ordencompracab
    ],200);
}
public function confirmar(Request $r, $id) {
    $ordenCompra = OrdenCompraCab::find($id);
    
    if (!$ordenCompra) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // Validación de los datos
    $datosValidados = $r->validate([
        'ord_comp_fecha' => 'required|date',
        'ord_comp_estado' => 'required',
        'condicion_pago' => 'required|string|max:20',
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer',
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
    ]);

    // Guardar datos de la orden de compra
    $ordenCompra->update($datosValidados);

    return response()->json([
        'mensaje' => 'Orden confirmada y detalle guardado con éxito',
        'tipo' => 'success',
        'registro' => $ordenCompra
    ], 200);
}
public function rechazar(Request $r, $id)
{
    $ordencompracab = OrdenCompraCab::find($id);
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // Validación de los campos requeridos
    $datosValidados = $r->validate([
        'ord_comp_estado' => 'required|string',
        'ord_comp_intervalo_fecha_vence' => 'nullable|date', // Si es opcional
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer',
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    // Si la condición de pago es 'CONTADO', se ajustan los campos correspondientes
    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
        $datosValidados['ord_comp_cant_cuota'] = null;
    }

    $ordencompracab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Orden de compra rechazada con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}

public function aprobar(Request $r, $id)
{
    $ordencompracab = OrdenCompraCab::find($id);
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // Validación de los campos requeridos
    $datosValidados = $r->validate([
        'ord_comp_estado' => 'required|string',
        'ord_comp_intervalo_fecha_vence' => 'nullable|date', // Si es opcional
        'user_id' => 'required|integer',
        'presupuesto_id' => 'required|integer',
        'proveedor_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'condicion_pago' => 'required|string|max:20'
    ]);

    // Si la condición de pago es 'CONTADO', se ajustan los campos correspondientes
    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['ord_comp_intervalo_fecha_vence'] = null;
        $datosValidados['ord_comp_cant_cuota'] = null;
    }

    $ordencompracab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Orden de compra aprobada con éxito',
        'tipo' => 'success',
        'registro' => $ordencompracab
    ], 200);
}
public function buscar(Request $r)
{
    // Obtener los parámetros de búsqueda desde la request
    $searchTerm = $r->input('search'); // Término de búsqueda

    // Realizar la búsqueda en la base de datos
    return DB::select("
        SELECT 
            o.id,
            COALESCE(to_char(o.ord_comp_intervalo_fecha_vence, 'dd/mm/yyyy HH24:mi:ss'), 'N/A') AS ord_comp_intervalo_fecha_vence,
            o.ord_comp_fecha,
            o.ord_comp_estado,
            COALESCE(o.ord_comp_cant_cuota::varchar, '0') AS ord_comp_cant_cuota,
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
            o.orde_compra_id,
            'ORDEN DE COMPRA NRO: ' || TO_CHAR(o.id, '0000000') || 
            ' VENCE EL: ' || TO_CHAR(o.comp_fecha_vence, 'dd/mm/yyyy HH24:mi:ss') || 
            ' (' || o.observaciones || ')' AS orden_compra
        FROM 
            orden_compra_cab o
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
        WHERE 
            o.id::text ILIKE ? OR 
            p.prov_razonsocial ILIKE ? OR 
            p.prov_ruc ILIKE ?
            AND o.ord_comp_estado = 'CONFIRMADO'
    ", ['%' . $searchTerm . '%', '%' . $searchTerm . '%', '%' . $searchTerm . '%']);
}
}
