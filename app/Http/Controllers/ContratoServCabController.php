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
                TO_CHAR(csc.contrato_fecha_inicio, 'DD/MM/YYYY')      AS contrato_fecha_inicio,
                TO_CHAR(csc.contrato_fecha_fin, 'DD/MM/YYYY')         AS contrato_fecha_fin,

                COALESCE(
                    TO_CHAR(csc.contrato_intervalo_fecha_vence, 'DD/MM/YYYY HH24:MI:SS'),
                    'N/A'
                ) AS contrato_intervalo_fecha_vence,

                csc.contrato_estado,
                csc.contrato_condicion_pago,
                COALESCE(csc.contrato_cuotas::TEXT, 'N/A') AS contrato_cuotas,

                csc.tipo_contrato_id,
                tc.tip_con_nombre,

                csc.contrato_objeto,
                csc.contrato_alcance,
                csc.contrato_responsabilidad,
                csc.contrato_garantia,
                csc.contrato_limitacion,
                csc.contrato_fuerza_mayor,
                csc.contrato_jurisdiccion,

                csc.contrato_observacion,
                csc.contrato_representante,
                csc.contrato_numero,
                csc.contrato_archivo_url,

                cli.id AS clientes_id,
                cli.cli_nombre,
                cli.cli_apellido,
                cli.cli_ruc,
                cli.cli_direccion,
                cli.cli_telefono,
                cli.cli_correo,

                csc.empresa_id,
                e.emp_razon_social,
                csc.sucursal_id,
                s.suc_razon_social,

                ts.id AS tipo_servicio_id,
                ts.tipo_serv_nombre,

                csc.orden_serv_cab_id,

                f.fun_nom || ' ' || f.fun_apellido AS funcionario

            FROM contrato_serv_cab csc
            JOIN funcionario f    ON f.id   = csc.funcionario_id
            JOIN empresa e        ON e.id   = csc.empresa_id
            JOIN sucursal s       ON s.id   = csc.sucursal_id
            JOIN tipo_servicio ts ON ts.id  = csc.tipo_servicio_id
            JOIN clientes cli     ON cli.id = csc.clientes_id
            JOIN tipo_contrato tc ON tc.id  = csc.tipo_contrato_id

            ORDER BY csc.id DESC
        ");
    }

    private function validationRules(): array
    {
        return [
            'contrato_fecha'                => 'required|date_format:d/m/Y H:i:s',
            'contrato_fecha_inicio'         => 'required|string',
            'contrato_fecha_fin'            => 'required|string',
            'contrato_intervalo_fecha_vence'=> 'nullable|date_format:d/m/Y H:i:s',
            'contrato_condicion_pago'       => 'required|in:CONTADO,CREDITO',
            'contrato_cuotas'               => 'nullable|integer|min:1',
            'tipo_contrato_id'              => 'required|integer|exists:tipo_contrato,id',
            'contrato_objeto'               => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_alcance'              => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_responsabilidad'      => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_garantia'             => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_limitacion'           => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_fuerza_mayor'         => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_jurisdiccion'         => ['nullable', 'string', 'not_regex:/[*<>{}|]/'],
            'contrato_observacion'          => ['nullable', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'contrato_representante'        => ['nullable', 'string', 'max:200', 'not_regex:/[*<>{}|]/'],
            'empresa_id'                    => 'required|integer|exists:empresa,id',
            'sucursal_id'                   => 'required|integer|exists:sucursal,id',
            'clientes_id'                   => 'required|integer|exists:clientes,id',
            'tipo_servicio_id'              => 'required|integer|exists:tipo_servicio,id',
            'orden_serv_cab_id'             => 'nullable|integer|exists:orden_serv_cab,id',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'contrato_fecha.required'              => 'La fecha del contrato es obligatoria.',
            'contrato_fecha.date_format'           => 'El formato de fecha no es válido (DD/MM/YYYY HH:MM:SS).',
            'contrato_fecha_inicio.required'       => 'La fecha de inicio es obligatoria.',
            'contrato_fecha_fin.required'          => 'La fecha de fin es obligatoria.',
            'contrato_condicion_pago.required'     => 'La condición de pago es obligatoria.',
            'contrato_condicion_pago.in'           => 'La condición de pago debe ser CONTADO o CREDITO.',
            'tipo_contrato_id.required'            => 'Debe seleccionar un tipo de contrato.',
            'tipo_contrato_id.exists'              => 'El tipo de contrato seleccionado no es válido.',
            'empresa_id.required'                  => 'La empresa es obligatoria.',
            'sucursal_id.required'                 => 'La sucursal es obligatoria.',
            'clientes_id.required'                 => 'Debe seleccionar un cliente.',
            'tipo_servicio_id.required'            => 'Debe seleccionar un tipo de servicio.',
        ];
    }

    private function buildData(Request $r): array
    {
        return [
            'contrato_fecha'                => $r->contrato_fecha,
            'contrato_fecha_inicio'         => $r->contrato_fecha_inicio,
            'contrato_fecha_fin'            => $r->contrato_fecha_fin,
            'contrato_intervalo_fecha_vence'=> $r->contrato_condicion_pago === 'CONTADO' ? null : $r->contrato_intervalo_fecha_vence,
            'contrato_condicion_pago'       => strtoupper($r->contrato_condicion_pago),
            'contrato_cuotas'               => $r->contrato_condicion_pago === 'CONTADO' ? null : $r->contrato_cuotas,
            'tipo_contrato_id'              => $r->tipo_contrato_id,
            'contrato_objeto'               => $r->contrato_objeto,
            'contrato_alcance'              => $r->contrato_alcance,
            'contrato_responsabilidad'      => $r->contrato_responsabilidad,
            'contrato_garantia'             => $r->contrato_garantia,
            'contrato_limitacion'           => $r->contrato_limitacion,
            'contrato_fuerza_mayor'         => $r->contrato_fuerza_mayor,
            'contrato_jurisdiccion'         => $r->contrato_jurisdiccion,
            'contrato_observacion'          => $r->contrato_observacion,
            'contrato_representante'        => $r->contrato_representante,
            'empresa_id'                    => $r->empresa_id,
            'sucursal_id'                   => $r->sucursal_id,
            'clientes_id'                   => $r->clientes_id,
            'tipo_servicio_id'              => $r->tipo_servicio_id,
            'orden_serv_cab_id'             => $r->orden_serv_cab_id ?: null,
        ];
    }

    private function normalize(Request $r): void
    {
        if ($r->contrato_intervalo_fecha_vence === '') {
            $r->merge(['contrato_intervalo_fecha_vence' => null]);
        }
        if ($r->contrato_condicion_pago === 'CONTADO') {
            $r->merge(['contrato_cuotas' => null, 'contrato_intervalo_fecha_vence' => null]);
        }
    }

    public function store(Request $r)
    {
        $this->normalize($r);
        $r->validate($this->validationRules(), $this->validationMessages());

        $contrato = ContratoServCab::create(
            array_merge($this->buildData($r), [
                'contrato_estado'      => 'PENDIENTE',
                'contrato_archivo_url' => null,
                'contrato_numero'      => null,
                'funcionario_id'       => auth()->user()->funcionario_id,
            ])
        );

        $contrato->contrato_numero = 'CONT-' . date('Y') . '-' . str_pad($contrato->id, 5, '0', STR_PAD_LEFT);
        $contrato->save();

        return response()->json([
            'mensaje'  => 'Contrato registrado con éxito. Número: ' . $contrato->contrato_numero,
            'tipo'     => 'success',
            'registro' => $contrato,
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $contrato = ContratoServCab::find($id);
        if (!$contrato) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $this->normalize($r);
        $r->validate($this->validationRules(), $this->validationMessages());

        $contrato->update($this->buildData($r));

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $contrato,
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $contrato = ContratoServCab::find($id);
        if (!$contrato) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($contrato->contrato_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El contrato ya se encuentra anulado', 'tipo' => 'warning'], 200);
        }

        $contrato->contrato_estado = 'ANULADO';
        $contrato->save();

        return response()->json([
            'mensaje'  => 'Contrato de servicio anulado con éxito',
            'tipo'     => 'success',
            'registro' => $contrato,
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $contrato = ContratoServCab::find($id);
        if (!$contrato) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($contrato->contrato_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se pueden confirmar contratos en estado PENDIENTE',
                'tipo'    => 'warning',
            ], 200);
        }

        $contrato->contrato_estado = 'CONFIRMADO';
        $contrato->save();

        return response()->json([
            'mensaje'  => 'Contrato confirmado con éxito. Número: ' . $contrato->contrato_numero,
            'tipo'     => 'success',
            'registro' => $contrato,
        ], 200);
    }

    public function renovar($id)
    {
        $original = ContratoServCab::find($id);
        if (!$original) {
            return response()->json(['mensaje' => 'Contrato no encontrado', 'tipo' => 'error'], 404);
        }

        if ($original->contrato_estado !== 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'Solo se pueden renovar contratos confirmados',
                'tipo'    => 'warning',
            ], 200);
        }

        $nuevo = ContratoServCab::create([
            'contrato_fecha'                => $original->contrato_fecha,
            'contrato_fecha_inicio'         => $original->contrato_fecha_inicio,
            'contrato_fecha_fin'            => $original->contrato_fecha_fin,
            'contrato_intervalo_fecha_vence'=> $original->contrato_intervalo_fecha_vence,
            'contrato_estado'               => 'PENDIENTE',
            'contrato_condicion_pago'       => $original->contrato_condicion_pago,
            'contrato_cuotas'               => $original->contrato_cuotas,
            'tipo_contrato_id'              => $original->tipo_contrato_id,
            'contrato_objeto'               => $original->contrato_objeto,
            'contrato_alcance'              => $original->contrato_alcance,
            'contrato_responsabilidad'      => $original->contrato_responsabilidad,
            'contrato_garantia'             => $original->contrato_garantia,
            'contrato_limitacion'           => $original->contrato_limitacion,
            'contrato_fuerza_mayor'         => $original->contrato_fuerza_mayor,
            'contrato_jurisdiccion'         => $original->contrato_jurisdiccion,
            'contrato_observacion'          => $original->contrato_observacion,
            'contrato_representante'        => $original->contrato_representante,
            'contrato_archivo_url'          => null,
            'contrato_numero'               => null,
            'empresa_id'                    => $original->empresa_id,
            'sucursal_id'                   => $original->sucursal_id,
            'clientes_id'                   => $original->clientes_id,
            'tipo_servicio_id'              => $original->tipo_servicio_id,
            'orden_serv_cab_id'             => $original->orden_serv_cab_id,
            'funcionario_id'                => auth()->user()->funcionario_id,
        ]);

        $nuevo->contrato_numero = 'CONT-' . date('Y') . '-' . str_pad($nuevo->id, 5, '0', STR_PAD_LEFT);
        $nuevo->save();

        $detalles = DB::table('contrato_serv_det')
            ->where('contrato_serv_cab_id', $id)
            ->get();

        foreach ($detalles as $det) {
            DB::table('contrato_serv_det')->insert([
                'contrato_serv_cab_id'             => $nuevo->id,
                'item_id'                          => $det->item_id,
                'tipo_impuesto_id'                 => $det->tipo_impuesto_id,
                'contrato_serv_det_cantidad'       => $det->contrato_serv_det_cantidad,
                'contrato_serv_det_costo'          => $det->contrato_serv_det_costo,
                'contrato_serv_det_cantidad_stock' => $det->contrato_serv_det_cantidad_stock,
                'created_at'                       => now(),
                'updated_at'                       => now(),
            ]);
        }

        return response()->json([
            'mensaje'  => 'Contrato renovado con éxito. Número: ' . $nuevo->contrato_numero,
            'tipo'     => 'success',
            'registro' => $nuevo,
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

        if ($contrato->contrato_estado !== 'CONFIRMADO') {
            abort(403, 'Solo se pueden imprimir contratos confirmados');
        }

        return view('contrato_servicio.imprimir', compact('contrato'));
    }
}
