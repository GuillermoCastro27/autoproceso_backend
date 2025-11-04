<?php

namespace App\Http\Controllers;

use App\Models\ContratoServCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ContratoServCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
            csc.id,
            TO_CHAR(csc.contrato_fecha, 'DD/MM/YYYY HH24:MI:SS') AS contrato_fecha,
            TO_CHAR(csc.contrato_fecha_inicio, 'DD/MM/YYYY') AS contrato_fecha_inicio,
            TO_CHAR(csc.contrato_fecha_fin, 'DD/MM/YYYY') AS contrato_fecha_fin,

            -- 🗓️ Mostrar N/A si no hay intervalo de vencimiento
            COALESCE(TO_CHAR(csc.contrato_intervalo_fecha_vence, 'DD/MM/YYYY HH24:MI:SS'), 'N/A') AS contrato_intervalo_fecha_vence,

            csc.contrato_estado,
            csc.contrato_condicion_pago,

            -- 💰 Mostrar N/A si no hay cuotas
            COALESCE(csc.contrato_cuotas::TEXT, 'N/A') AS contrato_cuotas,

            csc.contrato_observacion,
            csc.contrato_archivo_url,

            -- 🧾 Cliente
            cli.id AS clientes_id,
            cli.cli_nombre,
            cli.cli_apellido,
            cli.cli_ruc,
            cli.cli_direccion,
            cli.cli_telefono,
            cli.cli_correo,

            -- 🏢 Empresa y Sucursal
            csc.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            csc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,

            -- ⚙️ Tipo de Servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_serv_nombre,

            -- 👤 Usuario encargado
            u.name AS encargado

        FROM contrato_serv_cab csc
            JOIN users u ON u.id = csc.user_id
            JOIN empresa e ON e.id = csc.empresa_id
            JOIN sucursal s ON s.empresa_id = csc.sucursal_id
            JOIN tipo_servicio ts ON ts.id = csc.tipo_servicio_id
            JOIN clientes cli ON cli.id = csc.clientes_id

        ORDER BY csc.id DESC
    ");
}
public function store(Request $r)
{
    // 🔹 1. Normalizar datos antes de validar

    // Si el campo viene vacío, lo convertimos en null
    if ($r->contrato_intervalo_fecha_vence === '') {
        $r->merge(['contrato_intervalo_fecha_vence' => null]);
    }

    // Si la condición de pago es CONTADO, anulamos las cuotas y vencimiento
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 2. Validación de campos
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required|date',
        'contrato_fecha_inicio' => 'required|date',
        'contrato_fecha_fin' => 'required|date',
        'contrato_intervalo_fecha_vence' => 'nullable|date',
        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'user_id' => 'required|integer'
    ]);

    // 🔹 3. Reforzar coherencia lógica
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $datosValidados['contrato_cuotas'] = null;
        $datosValidados['contrato_intervalo_fecha_vence'] = null;
    }

    // 🔹 4. Crear registro
    $contrato = ContratoServCab::create([
        'contrato_fecha' => $r->contrato_fecha,
        'contrato_fecha_inicio' => $r->contrato_fecha_inicio,
        'contrato_fecha_fin' => $r->contrato_fecha_fin,
        'contrato_intervalo_fecha_vence' => $datosValidados['contrato_intervalo_fecha_vence'] ?? null,
        'contrato_estado' => strtoupper($r->contrato_estado),
        'contrato_condicion_pago' => strtoupper($r->contrato_condicion_pago),
        'contrato_cuotas' => $datosValidados['contrato_cuotas'] ?? null,
        'contrato_observacion' => $r->contrato_observacion,
        'empresa_id' => $r->empresa_id,
        'sucursal_id' => $r->sucursal_id,
        'clientes_id' => $r->clientes_id,
        'tipo_servicio_id' => $r->tipo_servicio_id,
        'user_id' => $r->user_id
    ]);

    $contrato->save();

    // 🔹 5. Respuesta JSON
    return response()->json([
        'mensaje' => 'Contrato registrado con éxito',
        'tipo' => 'success',
        'registro' => $contrato
    ], 200);
}
public function update(Request $r, $id)
{
    $contrato = ContratoServCab::find($id);

    if (!$contrato) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // 🔹 Normalizar valores vacíos
    if ($r->contrato_intervalo_fecha_vence === '') {
        $r->merge(['contrato_intervalo_fecha_vence' => null]);
    }

    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 Validar datos
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required|date',
        'contrato_fecha_inicio' => 'required|date',
        'contrato_fecha_fin' => 'required|date',
        'contrato_intervalo_fecha_vence' => 'nullable|date',
        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'user_id' => 'required|integer',
        'contrato_observacion' => 'nullable|string|max:200'
    ]);

    // 🔹 Ajustar coherencia lógica
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $datosValidados['contrato_cuotas'] = null;
        $datosValidados['contrato_intervalo_fecha_vence'] = null;
    }

    // 🔹 Actualizar registro
    $contrato->update($datosValidados);

    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $contrato
    ], 200);
}
public function anular(Request $r, $id)
{
    $contrato = ContratoServCab::find($id);

    if (!$contrato) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // 🔹 Normalizar valores de pago
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 Validar datos mínimos
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required|date',
        'contrato_fecha_inicio' => 'required|date',
        'contrato_fecha_fin' => 'required|date',
        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'user_id' => 'required|integer'
    ]);

    // 🔹 Actualizar estado a ANULADO
    $contrato->update([
        ...$datosValidados,
        'contrato_estado' => 'ANULADO'
    ]);

    return response()->json([
        'mensaje' => 'Contrato de servicio anulado con éxito',
        'tipo' => 'success',
        'registro' => $contrato
    ], 200);
}

public function confirmar(Request $r, $id)
{
    $contrato = ContratoServCab::find($id);

    if (!$contrato) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    // 🔹 Normalizar valores
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 Validar datos requeridos
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required|date',
        'contrato_fecha_inicio' => 'required|date',
        'contrato_fecha_fin' => 'required|date',
        'contrato_intervalo_fecha_vence' => 'nullable|date',
        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'user_id' => 'required|integer'
    ]);

    // 🔹 Actualizar estado a CONFIRMADO
    $contrato->update([
        ...$datosValidados,
        'contrato_estado' => 'CONFIRMADO'
    ]);

    return response()->json([
        'mensaje' => 'Contrato confirmado con éxito',
        'tipo' => 'success',
        'registro' => $contrato
    ], 200);
}

}
