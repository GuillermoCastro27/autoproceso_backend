<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\NotaRemiVent;
use App\Models\NotaRemiVentDet;

class NotaRemiVentController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            nrv.*,

            -- Fecha formateada
            TO_CHAR(nrv.nota_remi_vent_fecha, 'dd/mm/yyyy HH24:mi:ss') AS nota_remi_vent_fecha_formato,

            -- Datos del cliente
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            -- Sucursal
            nrv.sucursal_id,
            s.suc_razon_social AS suc_razon_social,

            -- Empresa
            nrv.empresa_id,
            e.emp_razon_social AS emp_razon_social,

            -- Venta relacionada (si existe)
            'VENTA NRO: ' || TO_CHAR(nrv.ventas_cab_id, '0000000') AS venta,
            TO_CHAR(nrv.ventas_cab_id, '0000000') AS nro_venta,

            -- Usuario
            f.fun_nom || ' ' || f.fun_apellido AS funcionario

        FROM nota_remi_vent nrv

        JOIN clientes c
            ON c.id = nrv.clientes_id

        JOIN sucursal s
            ON s.id = nrv.sucursal_id

        JOIN empresa e
            ON e.id = nrv.empresa_id

        LEFT JOIN ventas_cab v
            ON v.id = nrv.ventas_cab_id

        JOIN funcionario f ON f.id = nrv.funcionario_id

        ORDER BY nrv.id DESC
    ");
}
public function store(Request $r)
{
    // ===============================
    // 🔹 VALIDACIONES
    // ===============================
    $datosValidados = $r->validate([
        'nota_remi_vent_fecha'         => 'required',
        'nota_remi_vent_observaciones' => 'nullable|string',
        'nota_remi_vent_estado'        => 'required|string',

        'clientes_id'   => 'required|integer',
        'ventas_cab_id' => 'required|integer',

        'funcionario_id' => 'nullable',
        'empresa_id'  => 'required|integer',
        'sucursal_id' => 'required|integer',
    ]);

    DB::beginTransaction();

    try {

        // ===============================
        // 🔹 CABECERA NOTA REMISIÓN
        // ===============================
        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
        $notaRemision = NotaRemiVent::create($datosValidados);
        $notaRemision->save();

        // ===============================
        // 🔹 OBTENER DETALLE DE LA VENTA
        // ===============================
        $detallesVenta = DB::select("
            SELECT 
                vd.item_id,
                vd.vent_det_cantidad,
                vd.vent_det_precio
            FROM ventas_det vd
            WHERE vd.ventas_cab_id = ?
        ", [$r->ventas_cab_id]);

        // ===============================
        // 🔹 CREAR DETALLE DE REMISIÓN
        // ===============================
        foreach ($detallesVenta as $dv) {

            $detalle = new NotaRemiVentDet();
            $detalle->nota_remi_vent_id = $notaRemision->id;
            $detalle->item_id = $dv->item_id;
            $detalle->nota_remi_vent_det_cantidad = $dv->vent_det_cantidad;
            $detalle->nota_remi_vent_det_precio   = $dv->vent_det_precio;
            $detalle->save();
        }

        DB::commit();

        return response()->json([
            'mensaje'  => 'Nota de remisión creada correctamente',
            'tipo'     => 'success',
            'registro' => $notaRemision
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al crear la nota de remisión',
            'tipo'    => 'error',
            'error'   => $e->getMessage()
        ], 500);
    }
}
public function update(Request $r, $id)
{
    $nota = NotaRemiVent::find($id);

    if (!$nota) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    // Regla de negocio
    if ($nota->nota_remi_vent_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden modificar notas en estado PENDIENTE',
            'tipo'    => 'warning'
        ], 400);
    }

    $datosValidados = $r->validate([
        'nota_remi_vent_fecha'         => 'required|date',
        'nota_remi_vent_observaciones' => 'required|string',
        'clientes_id'                 => 'required|integer',
        'ventas_cab_id'               => 'required|integer',
        'empresa_id'                  => 'required|integer',
        'sucursal_id'                 => 'required|integer'
    ]);

    $nota->update($datosValidados);

    return response()->json([
        'mensaje'  => 'Nota de remisión modificada con éxito',
        'tipo'     => 'success',
        'registro' => $nota
    ], 200);
}
public function anular(Request $r, $id)
{
    $nota = NotaRemiVent::find($id);

    if (!$nota) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    $datosValidados = $r->validate([
        'nota_remi_vent_observaciones' => 'required|string',
    ]);

    $nota->nota_remi_vent_estado = 'ANULADA';
    $nota->nota_remi_vent_observaciones = $datosValidados['nota_remi_vent_observaciones'];
    $nota->save();

    return response()->json([
        'mensaje'  => 'Nota de remisión anulada correctamente',
        'tipo'     => 'success',
        'registro' => $nota
    ], 200);
}

public function confirmar(Request $r, $id)
{
    $nota = NotaRemiVent::find($id);

    if (!$nota) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    if ($nota->nota_remi_vent_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'La nota de remisión ya fue procesada',
            'tipo'    => 'warning'
        ], 400);
    }

    $datosValidados = $r->validate([
        'nota_remi_vent_observaciones' => 'required|string',
    ]);

    $nota->nota_remi_vent_estado = 'CONFIRMADA';
    $nota->nota_remi_vent_observaciones = $datosValidados['nota_remi_vent_observaciones'];
    $nota->save();

    return response()->json([
        'mensaje'  => 'Nota de remisión confirmada correctamente',
        'tipo'     => 'success',
        'registro' => $nota
    ], 200);
}


}
