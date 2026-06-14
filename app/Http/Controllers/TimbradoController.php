<?php

namespace App\Http\Controllers;

use App\Models\Timbrado;
use Illuminate\Http\Request;

class TimbradoController extends Controller
{
    public function read()
    {
        return response()->json(
            Timbrado::with(['tipoComprobante', 'empresa', 'sucursal'])
                ->select(
                    'timbrado.*',
                    \DB::raw("tc.tip_comp_nombre"),
                    \DB::raw("tc.tip_comp_abrev"),
                    \DB::raw("e.emp_razon_social"),
                    \DB::raw("s.suc_razon_social")
                )
                ->join('tipo_comprobante as tc', 'tc.id', '=', 'timbrado.tipo_comprobante_id')
                ->join('empresa as e',           'e.id',  '=', 'timbrado.empresa_id')
                ->join('sucursal as s',          's.id',  '=', 'timbrado.sucursal_id')
                ->orderByDesc('timbrado.created_at')
                ->get()
        );
    }

    /**
     * Devuelve el timbrado activo y el próximo número de comprobante
     * para una empresa, sucursal y tipo de comprobante dados.
     * No consume el número — solo lo muestra al usuario para preview.
     */
    public function siguiente(Request $r)
    {
        $r->validate([
            'empresa_id'          => 'required|integer',
            'sucursal_id'         => 'required|integer',
            'tipo_comprobante_id' => 'required|integer',
        ]);

        $timbrado = Timbrado::activo(
            (int) $r->empresa_id,
            (int) $r->sucursal_id,
            (int) $r->tipo_comprobante_id
        );

        if (!$timbrado) {
            return response()->json([
                'mensaje' => 'No hay un timbrado activo para esta empresa/sucursal y tipo de comprobante.',
                'tipo'    => 'error',
            ], 404);
        }

        return response()->json([
            'timbrado_id'      => $timbrado->id,
            'tim_numero'       => $timbrado->tim_numero,
            'tim_fecha_fin'    => $timbrado->tim_fecha_fin,
            'nro_comprobante'  => $timbrado->tim_nro_actual + 1,
            'nros_restantes'   => $timbrado->tim_nro_hasta - $timbrado->tim_nro_actual,
        ]);
    }

    /**
     * Devuelve el timbrado activo para un tipo de documento dado.
     *
     * tipo_documento: 'factura' | 'nota_credito' | 'nota_debito'
     *
     * Mapea el tipo_documento a un patrón de búsqueda en tip_comp_nombre.
     * Si no encuentra coincidencia exacta devuelve 404 — no hace fallback genérico,
     * porque asignar el timbrado equivocado es peor que mostrar el error.
     */
    public function paraVentas(Request $r)
    {
        $r->validate([
            'empresa_id'     => 'required|integer',
            'sucursal_id'    => 'required|integer',
            'tipo_documento' => 'required|in:factura,nota_credito,nota_debito,nota_remision_comp,nota_remision_vent',
        ], [
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'       => 'Tipo de documento inválido.',
        ]);

        $abreviaturas = [
            'factura'            => null,
            'nota_credito'       => 'NC',
            'nota_debito'        => 'ND',
            'nota_remision_comp' => 'NRC',
            'nota_remision_vent' => 'NRV',
        ];

        $patrones = [
            'factura'            => '%factura%',
            'nota_credito'       => '%cr%dito%',
            'nota_debito'        => '%d%bito%',
            'nota_remision_comp' => '%Remisi%Comp%',
            'nota_remision_vent' => '%Remisi%Vent%',
        ];

        $patron      = $patrones[$r->tipo_documento];
        $abreviatura = $abreviaturas[$r->tipo_documento];
        $hoy       = now()->toDateString();

        $query = Timbrado::join('tipo_comprobante as tc', 'tc.id', '=', 'timbrado.tipo_comprobante_id')
            ->where('timbrado.empresa_id',  (int) $r->empresa_id)
            ->where('timbrado.sucursal_id', (int) $r->sucursal_id)
            ->where('timbrado.tim_estado',  'activo')
            ->whereDate('timbrado.tim_fecha_inicio', '<=', $hoy)
            ->whereDate('timbrado.tim_fecha_fin',    '>=', $hoy)
            ->select('timbrado.*');

        if ($abreviatura) {
            $query->where(function($q) use ($abreviatura, $patron) {
                $q->where('tc.tip_comp_abrev', $abreviatura)
                  ->orWhereRaw('tc.tip_comp_nombre ILIKE ?', [$patron]);
            });
        } else {
            $query->whereRaw('LOWER(tc.tip_comp_nombre) LIKE ?', [$patron]);
        }

        $timbrado = $query->first();

        if (!$timbrado) {
            $label = [
                'factura'            => 'Factura',
                'nota_credito'       => 'Nota de Crédito',
                'nota_debito'        => 'Nota de Débito',
                'nota_remision_comp' => 'Nota de Remisión Comp',
                'nota_remision_vent' => 'Nota de Remisión Vent',
            ][$r->tipo_documento];

            return response()->json([
                'mensaje' => "No hay un timbrado activo de tipo \"{$label}\" para la empresa y sucursal seleccionadas.",
                'tipo'    => 'error',
            ], 404);
        }

        $nroSiguiente = $timbrado->tim_nro_actual + 1;
        return response()->json([
            'timbrado_id'          => $timbrado->id,
            'tim_numero'           => $timbrado->tim_numero,
            'tim_establecimiento'  => $timbrado->tim_establecimiento ?? '001',
            'tim_punto_expedicion' => $timbrado->tim_punto_expedicion ?? '001',
            'tim_fecha_fin'        => $timbrado->tim_fecha_fin,
            'nro_comprobante'      => $nroSiguiente,
            'nro_formateado'       => $timbrado->formatearComprobante($nroSiguiente),
            'nros_restantes'       => $timbrado->tim_nro_hasta - $timbrado->tim_nro_actual,
        ]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'tim_numero'            => ['required', 'string', 'max:20'],
            'tim_establecimiento'   => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
            'tim_punto_expedicion'  => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
            'tim_fecha_inicio'      => ['required', 'date'],
            'tim_fecha_fin'         => ['required', 'date', 'after_or_equal:tim_fecha_inicio'],
            'tim_nro_desde'         => ['required', 'integer', 'min:1'],
            'tim_nro_hasta'         => ['required', 'integer', 'gt:tim_nro_desde'],
            'tipo_comprobante_id'   => ['required', 'exists:tipo_comprobante,id'],
            'empresa_id'            => ['required', 'exists:empresa,id'],
            'sucursal_id'           => ['required', 'exists:sucursal,id'],
        ], [
            'tim_numero.required'              => 'El número de timbrado es obligatorio.',
            'tim_establecimiento.required'     => 'El código de establecimiento es obligatorio.',
            'tim_establecimiento.size'         => 'El establecimiento debe tener exactamente 3 dígitos.',
            'tim_establecimiento.regex'        => 'El establecimiento debe ser numérico (ej: 001).',
            'tim_punto_expedicion.required'    => 'El punto de expedición es obligatorio.',
            'tim_punto_expedicion.size'        => 'El punto de expedición debe tener exactamente 3 dígitos.',
            'tim_punto_expedicion.regex'       => 'El punto de expedición debe ser numérico (ej: 001).',
            'tim_fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
            'tim_fecha_fin.required'           => 'La fecha de fin es obligatoria.',
            'tim_fecha_fin.after_or_equal'     => 'La fecha de fin debe ser posterior a la de inicio.',
            'tim_nro_desde.required'           => 'El número desde es obligatorio.',
            'tim_nro_hasta.required'           => 'El número hasta es obligatorio.',
            'tim_nro_hasta.gt'                 => 'El número hasta debe ser mayor al número desde.',
            'tipo_comprobante_id.required'     => 'El tipo de comprobante es obligatorio.',
            'empresa_id.required'              => 'La empresa es obligatoria.',
            'sucursal_id.required'             => 'La sucursal es obligatoria.',
        ]);

        $registro = Timbrado::create([
            'tim_numero'            => $r->tim_numero,
            'tim_establecimiento'   => $r->tim_establecimiento,
            'tim_punto_expedicion'  => $r->tim_punto_expedicion,
            'tim_fecha_inicio'      => $r->tim_fecha_inicio,
            'tim_fecha_fin'         => $r->tim_fecha_fin,
            'tim_nro_desde'         => $r->tim_nro_desde,
            'tim_nro_hasta'         => $r->tim_nro_hasta,
            'tim_nro_actual'        => $r->tim_nro_desde - 1,
            'tim_estado'            => 'activo',
            'tipo_comprobante_id'   => $r->tipo_comprobante_id,
            'empresa_id'            => $r->empresa_id,
            'sucursal_id'           => $r->sucursal_id,
        ]);

        return response()->json(['mensaje' => 'Timbrado registrado con éxito', 'tipo' => 'success', 'registro' => $registro]);
    }

    public function update(Request $r, $id)
    {
        $registro = Timbrado::find($id);
        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        $r->validate([
            'tim_numero'            => ['required', 'string', 'max:20'],
            'tim_establecimiento'   => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
            'tim_punto_expedicion'  => ['required', 'string', 'size:3', 'regex:/^\d{3}$/'],
            'tim_fecha_inicio'      => ['required', 'date'],
            'tim_fecha_fin'         => ['required', 'date', 'after_or_equal:tim_fecha_inicio'],
            'tim_nro_desde'         => ['required', 'integer', 'min:1'],
            'tim_nro_hasta'         => ['required', 'integer', 'gt:tim_nro_desde'],
            'tim_estado'            => ['required', 'in:activo,agotado,vencido,cancelado'],
            'tipo_comprobante_id'   => ['required', 'exists:tipo_comprobante,id'],
            'empresa_id'            => ['required', 'exists:empresa,id'],
            'sucursal_id'           => ['required', 'exists:sucursal,id'],
        ], [
            'tim_fecha_fin.after_or_equal'  => 'La fecha de fin debe ser posterior a la de inicio.',
            'tim_nro_hasta.gt'              => 'El número hasta debe ser mayor al número desde.',
            'tim_estado.in'                 => 'Estado inválido.',
            'tim_establecimiento.size'      => 'El establecimiento debe tener exactamente 3 dígitos.',
            'tim_punto_expedicion.size'     => 'El punto de expedición debe tener exactamente 3 dígitos.',
        ]);

        $registro->update($r->only([
            'tim_numero', 'tim_establecimiento', 'tim_punto_expedicion',
            'tim_fecha_inicio', 'tim_fecha_fin',
            'tim_nro_desde', 'tim_nro_hasta', 'tim_estado',
            'tipo_comprobante_id', 'empresa_id', 'sucursal_id',
        ]));

        return response()->json(['mensaje' => 'Timbrado modificado con éxito', 'tipo' => 'success', 'registro' => $registro]);
    }

    public function destroy($id)
    {
        $registro = Timbrado::find($id);
        if (!$registro) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        try {
            $registro->delete();
            return response()->json(['mensaje' => 'Timbrado eliminado con éxito', 'tipo' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'No se puede eliminar porque tiene comprobantes asociados.', 'tipo' => 'error'], 409);
        }
    }
}
