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

            -- Cliente
            c.id AS clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Diagnóstico
            dg.id AS diagnostico_cab_id,
            dg.diag_cab_observaciones AS diagnostico,

            -- Tipo de diagnóstico
            td.id AS tipo_diagnostico_id,
            td.tipo_diag_nombre,

            -- Equipo de Trabajo 
            et.id AS equipo_trabajo_id,
            et.equipo_nombre,
            et.equipo_descripcion,
            et.equipo_categoria,

            -- Tipo de Vehículo
            tv.id AS tipo_vehiculo_id,
            tv.tip_veh_nombre,
            tv.tip_veh_capacidad,
            tv.tip_veh_combustible,
            tv.tip_veh_categoria,

            -- Marca y Modelo
            m.id AS marca_id,
            m.marc_nom AS marc_nom,
            mo.id AS modelo_id,
            mo.modelo_nom AS modelo_nom,

            -- Presupuesto relacionado
            psc.id AS presupuesto_serv_cab_id,
            'PRESUPUESTO SERV Nº: ' || to_char(psc.id, '0000000')
                || ' (' || COALESCE(psc.pres_serv_cab_observaciones, 'N/A') || ')' 
                AS presupuesto_serv,

            -- Empresa y sucursal
            osc.empresa_id,
            e.emp_razon_social,
            osc.sucursal_id,
            s.suc_razon_social,

            -- Encargado
            u.name AS encargado

        FROM orden_serv_cab osc
        JOIN users u              ON u.id = osc.user_id
        JOIN empresa e            ON e.id = osc.empresa_id
        JOIN sucursal s           ON s.empresa_id = osc.sucursal_id
        
        -- Relaciones directas ahora sí existen
        JOIN clientes c           ON c.id = osc.clientes_id
        JOIN diagnostico_cab dg   ON dg.id = osc.diagnostico_cab_id
        JOIN tipo_diagnostico td  ON td.id = osc.tipo_diagnostico_id
        JOIN equipo_trabajo et    ON et.id = osc.equipo_trabajo_id  
        JOIN tipo_vehiculo tv     ON tv.id = osc.tipo_vehiculo_id
        JOIN marca m              ON m.id = tv.marca_id
        JOIN modelo mo            ON mo.id = tv.modelo_id

        -- Presupuesto sigue siendo útil como referencia
        JOIN presupuesto_serv_cab psc ON psc.id = osc.presupuesto_serv_cab_id

        ORDER BY osc.id DESC
    ");
}
public function store(Request $r) 
{
    // Validación
    $datosValidados = $r->validate([
        'ord_serv_observaciones' => 'required',
        'ord_serv_fecha_vence' => 'required',
        'ord_serv_fecha' => 'required',
        'ord_serv_estado' => 'required',
        'ord_serv_tipo' => 'required',
        'user_id' => 'required',
        'presupuesto_serv_cab_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required',
        'diagnostico_cab_id' => 'required',
        'tipo_diagnostico_id' => 'required',
        'tipo_vehiculo_id' => 'required',
        'equipo_trabajo_id' => 'required'
    ]);

    // Crear la Orden de Servicio
    $ordenservcab = OrdenServCab::create($datosValidados);

    // Buscar presupuesto
    $presupuesto = PresupuestoServCab::find($r->presupuesto_serv_cab_id);

    if (!$presupuesto) {
        return response()->json([
            'mensaje' => 'Presupuesto no encontrado',
            'tipo' => 'error',
        ], 404);
    }

    // Copiar detalles del Presupuesto hacia la Orden de Servicio
    $detalles = DB::select("
        SELECT 
            psd.*, 
            i.item_decripcion,
            psd.pres_serv_det_costo AS orden_serv_det_costo,
            psd.pres_serv_det_cantidad AS orden_serv_det_cantidad,
            psd.pres_serv_det_cantidad_stock AS orden_serv_det_cantidad_stock,
            i.tipo_impuesto_id
        FROM presupuesto_serv_det psd  
        JOIN items i ON i.id = psd.item_id 
        WHERE psd.presupuesto_serv_cab_id = {$presupuesto->id}
    ");

    foreach ($detalles as $ocd) {
        OrdenServDet::create([
            'orden_serv_cab_id' => $ordenservcab->id,
            'item_id' => $ocd->item_id,
            'orden_serv_det_costo' => $ocd->orden_serv_det_costo,
            'orden_serv_det_cantidad' => $ocd->orden_serv_det_cantidad,
            'orden_serv_det_cantidad_stock' => $ocd->orden_serv_det_cantidad_stock,
            'tipo_impuesto_id' => $ocd->tipo_impuesto_id
        ]);
    }

    // Cambiar estado del presupuesto
    $presupuesto->pres_serv_cab_estado = "PROCESADO";
    $presupuesto->save();

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $ordenservcab
    ], 200);
}
public function update(Request $r, $id)
{
    $ordenservcab = OrdenServCab::find($id);

    if (!$ordenservcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    $datosValidados = $r->validate([
        'ord_serv_observaciones' => 'required',
        'ord_serv_fecha_vence' => 'required',
        'ord_serv_fecha' => 'required',
        'ord_serv_estado' => 'required',
        'ord_serv_tipo' => 'required',
        'user_id' => 'required',
        'presupuesto_serv_cab_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required',
        'diagnostico_cab_id' => 'required',
        'tipo_diagnostico_id' => 'required',
        'tipo_vehiculo_id' => 'required',
        'equipo_trabajo_id' => 'required'
    ]);

    $ordenservcab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $ordenservcab
    ], 200);
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

    $datosValidados = $r->validate([
        'ord_serv_observaciones' => 'required',
        'ord_serv_fecha_vence' => 'required',
        'ord_serv_fecha' => 'required',
        'ord_serv_estado' => 'required',
        'ord_serv_tipo' => 'required',
        'user_id' => 'required',
        'presupuesto_serv_cab_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required',
        'diagnostico_cab_id' => 'required',
        'tipo_diagnostico_id' => 'required',
        'tipo_vehiculo_id' => 'required',
        'equipo_trabajo_id' => 'required'
    ]);

    $ordenservcab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Orden de servicio anulada con éxito',
        'tipo' => 'success',
        'registro' => $ordenservcab
    ], 200);
}
    public function confirmar(Request $r, $id)
{
    $ordenservcab = OrdenServCab::find($id);

    if (!$ordenservcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    $datosValidados = $r->validate([
        'ord_serv_observaciones' => 'required',
        'ord_serv_fecha_vence' => 'required',
        'ord_serv_fecha' => 'required',
        'ord_serv_estado' => 'required',
        'ord_serv_tipo' => 'required',
        'user_id' => 'required',
        'presupuesto_serv_cab_id' => 'required',
        'clientes_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required',
        'diagnostico_cab_id' => 'required',
        'tipo_diagnostico_id' => 'required',
        'tipo_vehiculo_id' => 'required',
        'equipo_trabajo_id' => 'required'
    ]);

    $ordenservcab->update($datosValidados);

    return response()->json([
        'mensaje' => 'Registro confirmado con éxito',
        'tipo' => 'success',
        'registro' => $ordenservcab
    ], 200);
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
