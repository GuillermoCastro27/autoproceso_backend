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

            -- Fechas
            TO_CHAR(csc.contrato_fecha, 'DD/MM/YYYY HH24:MI:SS') AS contrato_fecha,
            TO_CHAR(csc.contrato_fecha_inicio, 'DD/MM/YYYY') AS contrato_fecha_inicio,
            TO_CHAR(csc.contrato_fecha_fin, 'DD/MM/YYYY') AS contrato_fecha_fin,

            COALESCE(
                TO_CHAR(csc.contrato_intervalo_fecha_vence, 'DD/MM/YYYY HH24:MI:SS'),
                'N/A'
            ) AS contrato_intervalo_fecha_vence,

            -- Estado y pago
            csc.contrato_estado,
            csc.contrato_condicion_pago,
            COALESCE(csc.contrato_cuotas::TEXT, 'N/A') AS contrato_cuotas,

            -- Tipo de contrato (FK + nombre)
            csc.tipo_contrato_id,
            tc.tip_con_nombre AS tip_con_nombre,

            -- Contenido contractual
            csc.contrato_objeto,
            csc.contrato_alcance,
            csc.contrato_responsabilidad,
            csc.contrato_garantia,
            csc.contrato_limitacion,
            csc.contrato_fuerza_mayor,
            csc.contrato_jurisdiccion,

            -- Otros
            csc.contrato_observacion,
            csc.contrato_archivo_url,

            -- Cliente
            cli.id AS clientes_id,
            cli.cli_nombre,
            cli.cli_apellido,
            cli.cli_ruc,
            cli.cli_direccion,
            cli.cli_telefono,
            cli.cli_correo,

            -- Empresa y Sucursal
            csc.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            csc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,

            -- Tipo de Servicio
            ts.id AS tipo_servicio_id,
            ts.tipo_serv_nombre AS tipo_serv_nombre,

            -- Orden de servicio vinculada
            csc.orden_serv_cab_id,

            -- Usuario
            f.fun_nom || ' ' || f.fun_apellido AS funcionario

        FROM contrato_serv_cab csc
            JOIN funcionario f     ON f.id = csc.funcionario_id
            JOIN empresa e         ON e.id = csc.empresa_id
            JOIN sucursal s        ON s.id = csc.sucursal_id
            JOIN tipo_servicio ts  ON ts.id = csc.tipo_servicio_id
            JOIN clientes cli      ON cli.id = csc.clientes_id
            JOIN tipo_contrato tc  ON tc.id = csc.tipo_contrato_id

        ORDER BY csc.id DESC
    ");
}

public function store(Request $r)
{
    // 🔹 1. Normalizar datos antes de validar
    if ($r->contrato_intervalo_fecha_vence === '') {
        $r->merge(['contrato_intervalo_fecha_vence' => null]);
    }

    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 2. Validación
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required',
        'contrato_fecha_inicio' => 'required',
        'contrato_fecha_fin' => 'required',
        'contrato_intervalo_fecha_vence' => 'nullable|date',

        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',

        // 🟢 NUEVO: tipo de contrato por FK
        'tipo_contrato_id' => 'required|integer|exists:tipo_contrato,id',

        'contrato_objeto' => 'nullable|string',
        'contrato_alcance' => 'nullable|string',
        'contrato_responsabilidad' => 'nullable|string',
        'contrato_garantia' => 'nullable|string',
        'contrato_limitacion' => 'nullable|string',
        'contrato_fuerza_mayor' => 'nullable|string',
        'contrato_jurisdiccion' => 'nullable|string',

        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'funcionario_id' => 'nullable',
        'orden_serv_cab_id' => 'nullable|integer|exists:orden_serv_cab,id',
    ]);

    // 🔹 3. Coherencia lógica adicional
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $datosValidados['contrato_cuotas'] = null;
        $datosValidados['contrato_intervalo_fecha_vence'] = null;
    }

    // 🔹 4. Crear contrato
    $contrato = ContratoServCab::create([
        'contrato_fecha' => $r->contrato_fecha,
        'contrato_fecha_inicio' => $r->contrato_fecha_inicio,
        'contrato_fecha_fin' => $r->contrato_fecha_fin,
        'contrato_intervalo_fecha_vence' => $datosValidados['contrato_intervalo_fecha_vence'] ?? null,

        'contrato_estado' => strtoupper($r->contrato_estado),
        'contrato_condicion_pago' => strtoupper($r->contrato_condicion_pago),
        'contrato_cuotas' => $datosValidados['contrato_cuotas'] ?? null,

        // 🟢 NUEVO
        'tipo_contrato_id' => $r->tipo_contrato_id,

        'contrato_objeto' => $r->contrato_objeto,
        'contrato_alcance' => $r->contrato_alcance,
        'contrato_responsabilidad' => $r->contrato_responsabilidad,
        'contrato_garantia' => $r->contrato_garantia,
        'contrato_limitacion' => $r->contrato_limitacion,
        'contrato_fuerza_mayor' => $r->contrato_fuerza_mayor,
        'contrato_jurisdiccion' => $r->contrato_jurisdiccion,

        'contrato_observacion' => $r->contrato_observacion,
        'contrato_archivo_url' => null,

        'empresa_id' => $r->empresa_id,
        'sucursal_id' => $r->sucursal_id,
        'clientes_id' => $r->clientes_id,
        'tipo_servicio_id' => $r->tipo_servicio_id,
        'funcionario_id' => auth()->user()->funcionario_id,
        'orden_serv_cab_id' => $r->orden_serv_cab_id ?: null,
    ]);

    // 🔹 5. Respuesta
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

    // 🔹 Validación
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required',
        'contrato_fecha_inicio' => 'required',
        'contrato_fecha_fin' => 'required',
        'contrato_intervalo_fecha_vence' => 'nullable|date',

        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',

        // 🟢 NUEVO
        'tipo_contrato_id' => 'required|integer|exists:tipo_contrato,id',

        'contrato_objeto' => 'nullable|string',
        'contrato_alcance' => 'nullable|string',
        'contrato_responsabilidad' => 'nullable|string',
        'contrato_garantia' => 'nullable|string',
        'contrato_limitacion' => 'nullable|string',
        'contrato_fuerza_mayor' => 'nullable|string',
        'contrato_jurisdiccion' => 'nullable|string',

        'contrato_observacion' => 'nullable|string|max:200',

        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer',
        'orden_serv_cab_id' => 'nullable|integer|exists:orden_serv_cab,id',
    ]);

    // 🔹 Coherencia lógica final
    if ($r->contrato_condicion_pago === 'CONTADO') {
        $datosValidados['contrato_cuotas'] = null;
        $datosValidados['contrato_intervalo_fecha_vence'] = null;
    }

    // 🔹 Actualizar
    $contrato->update([
        'contrato_fecha' => $r->contrato_fecha,
        'contrato_fecha_inicio' => $r->contrato_fecha_inicio,
        'contrato_fecha_fin' => $r->contrato_fecha_fin,
        'contrato_intervalo_fecha_vence' => $datosValidados['contrato_intervalo_fecha_vence'] ?? null,

        'contrato_estado' => strtoupper($r->contrato_estado),
        'contrato_condicion_pago' => strtoupper($r->contrato_condicion_pago),
        'contrato_cuotas' => $datosValidados['contrato_cuotas'] ?? null,

        // 🟢 NUEVO
        'tipo_contrato_id' => $r->tipo_contrato_id,

        'contrato_objeto' => $r->contrato_objeto,
        'contrato_alcance' => $r->contrato_alcance,
        'contrato_responsabilidad' => $r->contrato_responsabilidad,
        'contrato_garantia' => $r->contrato_garantia,
        'contrato_limitacion' => $r->contrato_limitacion,
        'contrato_fuerza_mayor' => $r->contrato_fuerza_mayor,
        'contrato_jurisdiccion' => $r->contrato_jurisdiccion,

        'contrato_observacion' => $r->contrato_observacion,

        'empresa_id' => $r->empresa_id,
        'sucursal_id' => $r->sucursal_id,
        'clientes_id' => $r->clientes_id,
        'tipo_servicio_id' => $r->tipo_servicio_id,
        'orden_serv_cab_id' => $r->orden_serv_cab_id ?: null,
    ]);

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

    // Evitar doble anulación
    if ($contrato->contrato_estado === 'ANULADO') {
        return response()->json([
            'mensaje' => 'El contrato ya se encuentra anulado',
            'tipo' => 'warning'
        ], 200);
    }

    // 🔹 Normalizar valores
    if ($r->contrato_intervalo_fecha_vence === '') {
        $r->merge(['contrato_intervalo_fecha_vence' => null]);
    }

    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 Validación COMPLETA
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required',
        'contrato_fecha_inicio' => 'required',
        'contrato_fecha_fin' => 'required',
        'contrato_intervalo_fecha_vence' => 'nullable|date',

        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',

        // 🟢 FK
        'tipo_contrato_id' => 'required|integer|exists:tipo_contrato,id',

        'contrato_objeto' => 'nullable|string',
        'contrato_alcance' => 'nullable|string',
        'contrato_responsabilidad' => 'nullable|string',
        'contrato_garantia' => 'nullable|string',
        'contrato_limitacion' => 'nullable|string',
        'contrato_fuerza_mayor' => 'nullable|string',
        'contrato_jurisdiccion' => 'nullable|string',

        'contrato_observacion' => 'nullable|string|max:200',

        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer'
    ]);

    // 🔹 Actualizar todo + estado ANULADO
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

    // Solo se puede confirmar si está pendiente
    if ($contrato->contrato_estado !== 'PENDIENTE') {
        return response()->json([
            'mensaje' => 'Solo se pueden confirmar contratos en estado PENDIENTE',
            'tipo' => 'warning'
        ], 200);
    }

    // 🔹 Normalizar valores
    if ($r->contrato_intervalo_fecha_vence === '') {
        $r->merge(['contrato_intervalo_fecha_vence' => null]);
    }

    if ($r->contrato_condicion_pago === 'CONTADO') {
        $r->merge([
            'contrato_cuotas' => null,
            'contrato_intervalo_fecha_vence' => null
        ]);
    }

    // 🔹 Validación COMPLETA
    $datosValidados = $r->validate([
        'contrato_fecha' => 'required',
        'contrato_fecha_inicio' => 'required',
        'contrato_fecha_fin' => 'required',
        'contrato_intervalo_fecha_vence' => 'nullable|date',

        'contrato_estado' => 'required|string|max:20',
        'contrato_condicion_pago' => 'required|string|max:20',
        'contrato_cuotas' => 'nullable|integer|min:1',

        // 🟢 FK
        'tipo_contrato_id' => 'required|integer|exists:tipo_contrato,id',

        'contrato_objeto' => 'nullable|string',
        'contrato_alcance' => 'nullable|string',
        'contrato_responsabilidad' => 'nullable|string',
        'contrato_garantia' => 'nullable|string',
        'contrato_limitacion' => 'nullable|string',
        'contrato_fuerza_mayor' => 'nullable|string',
        'contrato_jurisdiccion' => 'nullable|string',

        'contrato_observacion' => 'nullable|string|max:200',

        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'clientes_id' => 'required|integer',
        'tipo_servicio_id' => 'required|integer'
    ]);

    // 🔹 Actualizar todo + estado CONFIRMADO
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

public function imprimir($id)
{
    $contrato = \App\Models\ContratoServCab::with([
        'empresa',
        'sucursal',
        'cliente',
        'tipoServicio',
        'user'
    ])->findOrFail($id);

    // 🔒 Solo permitir imprimir contratos CONFIRMADOS
    if ($contrato->contrato_estado !== 'CONFIRMADO') {
        abort(403, 'Solo se pueden imprimir contratos confirmados');
    }

    return view('contrato_servicio.imprimir', compact('contrato'));
}

}
