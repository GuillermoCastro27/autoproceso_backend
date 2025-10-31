<?php

namespace App\Http\Controllers;

use App\Models\OrdenServCab;
use App\Models\PresupuestoServCab;
use App\Models\OrdenServDet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenServCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            osc.id,
            osc.ord_serv_fecha,
            osc.ord_serv_fecha_vence,
            osc.ord_serv_observaciones,
            osc.ord_serv_estado,
            osc.ord_serv_tipo,
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,       
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,
            s.suc_razon_social AS suc_razon_social,
            osc.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            psc.id AS presupuesto_serv_cab_id,
            'PRESUPUESTO SERV NRO: ' || to_char(psc.id, '0000000') || ' VENCE EL: ' || COALESCE(to_char(psc.pres_serv_cab_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') || ' (' || psc.pres_serv_cab_observaciones || ')' AS presupuesto_serv,
            u.name AS encargado  
        FROM 
            orden_serv_cab osc 
        JOIN 
            users u ON u.id = osc.user_id
        JOIN 
            sucursal s ON s.empresa_id = osc.sucursal_id
        JOIN 
            empresa e ON e.id = osc.empresa_id
        JOIN 
            presupuesto_serv_cab psc  ON psc.id = osc.presupuesto_serv_cab_id 
        JOIN 
            clientes c  ON c.id = psc.clientes_id
    ");
}
public function store(Request $r) {

    $datosValidados = $r->validate([
        'ord_serv_observaciones' => 'required',
        'ord_serv_fecha_vence' => 'required',
        'ord_serv_fecha' => 'required',
        'ord_serv_estado' => 'required',
        'ord_serv_tipo' => 'required',
        'user_id' => 'required',
        'presupuesto_serv_cab_id' => 'required', // Cambiado a presupuestos_id
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    $ordenservcab = OrdenServCab::create($datosValidados);
    $ordenservcab->save();

    // Buscar el presupuesto por su ID
    $presupuestoservcab = PresupuestoServCab::find($r->presupuesto_serv_cab_id); // Cambiado a presupuestos_id

    // Verificar si el presupuesto existe
    if (!$presupuestoservcab) {
        return response()->json([
            'mensaje' => 'Presupuesto no encontrado',
            'tipo' => 'error',
        ], 404);
    }

    // Actualizar el estado del presupuesto a "PROCESADO"
    $presupuestoservcab->pres_serv_cab_estado = "PROCESADO"; // Cambiado a pre_estado
    $presupuestoservcab->save();

    // Lógica para guardar detalles
    $detalles = DB::select("SELECT 
    psd.*, 
    i.item_decripcion,
    psd.pres_serv_det_costo as orden_serv_det_costo,
    psd.pres_serv_det_cantidad as orden_serv_det_cantidad,
    psd.pres_serv_det_cantidad_stock as orden_serv_det_cantidad_stock,
    i.tipo_impuesto_id
    FROM  presupuesto_serv_det psd  
    JOIN items i ON i.id = psd.item_id 
    WHERE psd.presupuesto_serv_cab_id = $presupuestoservcab->id;");

    foreach ($detalles as $ocd) {
        $ordenservdet = new OrdenServDet();
        $ordenservdet->orden_serv_cab_id = $ordenservcab->id;
        $ordenservdet->item_id = $ocd->item_id;
        $ordenservdet->orden_serv_det_costo = $ocd->orden_serv_det_costo;
        $ordenservdet->orden_serv_det_cantidad = $ocd->orden_serv_det_cantidad;
        $ordenservdet->orden_serv_det_cantidad_stock = $ocd->orden_serv_det_cantidad_stock;
        $ordenservdet->tipo_impuesto_id = $ocd->tipo_impuesto_id;
        $ordenservdet->save();
    }

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $ordenservcab
    ], 200);
}
public function update(Request $r, $id){
        $ordenservcab = OrdenServCab::find($id);
        if(!$ordenservcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ord_serv_observaciones' => 'required',
            'ord_serv_fecha_vence' => 'required',
            'ord_serv_fecha' => 'required',
            'ord_serv_estado' => 'required',
            'ord_serv_tipo' => 'required',
            'user_id' => 'required',
            'presupuesto_serv_cab_id' => 'required', // Cambiado a presupuestos_id
            'clientes_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);
        $ordenservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $ordenservcab
        ],200);
    }
    public function anular(Request $r, $id)
    {
        $ordenservcab = OrdenServCab::find($id);
        if (!$ordenservcab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        // ⚠️ Solo validamos los campos realmente necesarios
        $datosValidados = $r->validate([
            'ord_serv_observaciones' => 'required',
            'ord_serv_fecha_vence' => 'required',
            'ord_serv_fecha' => 'required',
            'ord_serv_estado' => 'required',
            'ord_serv_tipo' => 'required',
            'user_id' => 'required',
            'presupuesto_serv_cab_id' => 'required', // Cambiado a presupuestos_id
            'clientes_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);
        $ordenservcab->update($datosValidados);
        return response()->json([
            'mensaje' => 'Orden de servicio anulada con éxito',
            'tipo' => 'success',
            'registro' => $ordenservcab
        ], 200);
    }
    public function confirmar(Request $r, $id){
        $ordenservcab = OrdenServCab::find($id);
        if(!$ordenservcab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'ord_serv_observaciones' => 'required',
            'ord_serv_fecha_vence' => 'required',
            'ord_serv_fecha' => 'required',
            'ord_serv_estado' => 'required',
            'ord_serv_tipo' => 'required',
            'user_id' => 'required',
            'presupuesto_serv_cab_id' => 'required', // Cambiado a presupuestos_id
            'clientes_id' => 'required',
            'empresa_id' => 'required',
            'sucursal_id' => 'required'
        ]);
        $ordenservcab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $ordenservcab
        ],200);
    }
    public function buscar(Request $r){
        return DB::select("SELECT 
            rc.id AS recep_cab_id,
            rc.recep_cab_observaciones,
            rc.recep_cab_estado,
            rc.recep_cab_prioridad,
            rc.recep_cab_kilometraje,
            rc.recep_cab_nivel_combustible,
            rc.user_id,
            u.name AS encargado,
            u.login,
            rc.created_at,
            rc.updated_at,

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Tipo de servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_servicio,

            -- Empresa y sucursal
            rc.sucursal_id,
            s.suc_razon_social,
            rc.empresa_id,
            e.emp_razon_social,

            -- Texto descriptivo de la solicitud
            'RECEPCION NRO: ' || TO_CHAR(rc.id, '0000000') || 
            ' (' || rc.recep_cab_observaciones || ')' AS recepcion

        FROM 
            recep_cab rc 
        JOIN users u ON u.id = rc.user_id
        JOIN clientes c ON c.id = rc.clientes_id
        JOIN tipo_servicio ts ON ts.id = rc.tipo_servicio_id
        JOIN sucursal s ON s.empresa_id = rc.sucursal_id
        JOIN empresa e ON e.id = rc.empresa_id
        WHERE 
            rc.recep_cab_estado = 'CONFIRMADO'
    and rc.user_id = {$r->user_id} and u.name ilike'%{$r->name}%'");
    }
}
