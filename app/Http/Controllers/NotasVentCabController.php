<?php

namespace App\Http\Controllers;

use App\Models\NotasVentCab;
use App\Models\NotasVentDet;
use App\Models\VentasCab;
use App\Models\CtasCobrar;
use App\Models\LibroVentas;
use App\Models\Cliente;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class NotasVentCabController extends Controller
{
    /* ===============================
     * 📌 READ
     * =============================== */
    public function read()
    {
        return DB::select("
            SELECT
                nvc.id,
                nvc.empresa_id,
                nvc.sucursal_id,
                nvc.ventas_cab_id,
                nvc.clientes_id,
                nvc.timbrado_id,
                COALESCE(nvc.nota_vent_nro_comprobante::text, '') AS nota_vent_nro_comprobante,
                COALESCE(t.tim_numero::varchar, '')          AS tim_numero,
                COALESCE(TO_CHAR(t.tim_fecha_fin, 'YYYY-MM-DD'), '') AS tim_fecha_fin,
                COALESCE('VENTA NRO: ' || to_char(vc.id, '0000000'), 'SIN VENTA') AS venta,
                to_char(nvc.nota_vent_fecha, 'YYYY-MM-DD HH24:MI:SS') AS nota_vent_fecha,
                COALESCE(to_char(nvc.nota_vent_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS vencimiento,
                nvc.nota_vent_estado,
                nvc.nota_vent_tipo,
                nvc.nota_vent_afecta_stock,
                nvc.nota_vent_observaciones,
                nvc.nota_vene_condicion_pago,
                COALESCE(nvc.nota_vent_cant_cuota::varchar, '0') AS cuotas,
                c.cli_nombre,
                c.cli_apellido,
                c.cli_ruc,
                c.cli_direccion,
                c.cli_telefono,
                c.cli_correo,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social,
                e.emp_razon_social,
                nvc.created_at
            FROM notas_vent_cab nvc
            JOIN funcionario f  ON f.id  = nvc.funcionario_id
            JOIN clientes c     ON c.id  = nvc.clientes_id
            JOIN sucursal s     ON s.id  = nvc.sucursal_id
            JOIN empresa e      ON e.id  = nvc.empresa_id
            LEFT JOIN ventas_cab vc ON vc.id = nvc.ventas_cab_id
            LEFT JOIN timbrado t    ON t.id  = nvc.timbrado_id
        ");
    }


    /* ===============================
     * 📌 STORE
     * =============================== */
    public function store(Request $r)
    {
        if ($r->nota_vene_condicion_pago === 'CONTADO') {
            $r->merge([
                'nota_vent_intervalo_fecha_vence' => null,
                'nota_vent_cant_cuota' => null
            ]);
        }

        $datos = $r->validate([
            'nota_vent_intervalo_fecha_vence' => 'nullable',
            'nota_vent_fecha'                 => 'required',
            'nota_vent_estado'                => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'nota_vent_cant_cuota'            => 'nullable|integer',
            'nota_vent_tipo'                  => 'required|in:Crédito,Débito',
            'nota_vent_afecta_stock'          => 'nullable|boolean',
            'nota_vent_observaciones'         => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'nota_vene_condicion_pago'        => 'required|in:CONTADO,CRÉDITO',
            'clientes_id'                     => 'required|integer|exists:clientes,id',
            'ventas_cab_id'                   => 'required|integer|exists:ventas_cab,id',
            'funcionario_id'                  => 'nullable',
            'empresa_id'                      => 'required|integer|exists:empresa,id',
            'sucursal_id'                     => 'required|integer|exists:sucursal,id',
            'timbrado_id'                     => 'nullable|integer|exists:timbrado,id',
            'nota_vent_nro_comprobante'       => 'nullable|integer',
        ], [
            'nota_vent_estado.in'              => 'El estado no es válido.',
            'nota_vent_tipo.in'                => 'El tipo debe ser Crédito o Débito.',
            'nota_vent_observaciones.not_regex'=> 'Las observaciones contienen caracteres no permitidos.',
            'nota_vent_observaciones.max'      => 'Las observaciones no pueden superar 500 caracteres.',
            'nota_vene_condicion_pago.in'      => 'La condición de pago debe ser CONTADO o CRÉDITO.',
            'clientes_id.exists'               => 'El cliente seleccionado no es válido.',
            'ventas_cab_id.exists'             => 'La venta seleccionada no es válida.',
            'empresa_id.exists'                => 'La empresa seleccionada no es válida.',
            'sucursal_id.exists'               => 'La sucursal seleccionada no es válida.',
        ]);
        $datos['nota_vent_afecta_stock'] = filter_var($r->nota_vent_afecta_stock ?? true, FILTER_VALIDATE_BOOLEAN);

        DB::beginTransaction();

        try {
            $datos['funcionario_id'] = auth()->user()->funcionario_id;
            $nota = NotasVentCab::create($datos);

            // Cambiar estado de la venta
            $venta = VentasCab::find($r->ventas_cab_id);
            if ($venta) {
                $venta->vent_estado = 'PROCESADO';
                $venta->save();

                // Copiar detalle de venta
                $detalles = DB::table('ventas_det')
                    ->where('ventas_cab_id', $venta->id)
                    ->get();

                foreach ($detalles as $det) {
                    NotasVentDet::create([
                        'notas_vent_cab_id'       => $nota->id,
                        'item_id'                 => $det->item_id,
                        'tipo_impuesto_id'        => $det->tipo_impuesto_id,
                        'notas_vent_det_cantidad' => $det->vent_det_cantidad,
                        'notas_vent_det_precio'   => $det->vent_det_precio,
                        'deposito_id'             => $det->deposito_id ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Nota de venta registrada con éxito',
                'tipo' => 'success',
                'registro' => $nota
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'mensaje' => 'Error al registrar nota de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmar(Request $r, $id)
{
    DB::beginTransaction();

    try {

        $nota = NotasVentCab::find($id);

        if (!$nota) {
            return response()->json([
                'mensaje' => 'Nota de venta no encontrada',
                'tipo' => 'error'
            ], 404);
        }

        // 🔒 Evitar doble confirmación
        if ($nota->nota_vent_estado === 'CONFIRMADO') {
            return response()->json([
                'mensaje' => 'La nota de venta ya fue confirmada',
                'tipo' => 'warning'
            ], 400);
        }

        // ===============================
        // AJUSTES POR CONDICIÓN DE PAGO
        // ===============================
        if ($nota->nota_vene_condicion_pago === 'CONTADO') {
            $nota->nota_vent_intervalo_fecha_vence = null;
            $nota->nota_vent_cant_cuota = null;
        }

        // ===============================
        // CONFIRMAR ESTADO
        // ===============================
        $nota->nota_vent_estado = 'CONFIRMADO';
        $nota->save();

        // ===============================
        // DETALLES (ELOQUENT + IMPUESTO)
        // ===============================
        $detalles = NotasVentDet::where('notas_vent_cab_id', $nota->id)
            ->with('tipoImpuesto')
            ->get();

        if ($detalles->isEmpty()) {
            throw new \Exception('La nota no tiene detalles.');
        }

        // ===============================
        // CALCULAR IVA TOTAL
        // ===============================
        $totalIVA = 0;

        foreach ($detalles as $detalle) {

            $subtotal = $detalle->notas_vent_det_cantidad * $detalle->notas_vent_det_precio;

            if ($detalle->tipoImpuesto->tip_imp_nom === 'IVA10') {
                $totalIVA += $subtotal / 11;
            } elseif ($detalle->tipoImpuesto->tip_imp_nom === 'IVA5') {
                $totalIVA += $subtotal / 21;
            }

            // ===============================
            // AJUSTE DE STOCK (solo si afecta stock)
            // ===============================
            if ($nota->nota_vent_afecta_stock) {
                if ($nota->nota_vent_tipo === 'Crédito') {
                    // DEVOLUCIÓN: suma stock
                    $this->agregarAlStock(
                        $detalle->deposito_id,
                        $detalle->item_id,
                        $detalle->notas_vent_det_cantidad
                    );
                } else {
                    // DÉBITO: resta stock
                    $this->restarDeStock(
                        $detalle->deposito_id,
                        $detalle->item_id,
                        $detalle->notas_vent_det_cantidad
                    );
                }
            }
        }

        // ===============================
        // LIBRO DE VENTAS (AJUSTE)
        // ===============================
        $libro = LibroVentas::where('ventas_cab_id', $nota->ventas_cab_id)->first();

        if (!$libro) {
            throw new \Exception(
                'No existe Libro de Ventas para la venta asociada.'
            );
        }

        if ($nota->nota_vent_tipo === 'Crédito') {
            $libro->libV_monto -= $totalIVA;
        } else {
            $libro->libV_monto += $totalIVA;
        }

        if ($libro->libV_monto < 0) {
            $libro->libV_monto = 0;
        }

        $libro->updated_at = now();
        $libro->save();

        // ===============================
        // CTAS A COBRAR (AJUSTE)
        // ===============================
        $cta = CtasCobrar::where('ventas_cab_id', $nota->ventas_cab_id)
            ->where('cta_cob_estado', 'PENDIENTE')
            ->orderBy('nro_cuota')
            ->first();

        if ($cta) {

            if ($nota->nota_vent_tipo === 'Crédito') {
                $cta->cta_cob_monto -= $totalIVA;
            } else {
                $cta->cta_cob_monto += $totalIVA;
            }

            if ($cta->cta_cob_monto < 0) {
                $cta->cta_cob_monto = 0;
            }

            $cta->save();
        }

        DB::commit();

        return response()->json([
            'mensaje' => 'Nota de venta confirmada correctamente',
            'tipo' => 'success',
            'registro' => $nota
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al confirmar nota de venta',
            'error' => $e->getMessage()
        ], 500);
    }
}


protected function agregarAlStock($depositoId, $itemId, $cantidad)
{
    $stock = DB::table('stock')
        ->where('deposito_id', $depositoId)
        ->where('item_id', $itemId)
        ->first();

    if ($stock) {
        DB::table('stock')
            ->where('deposito_id', $depositoId)
            ->where('item_id', $itemId)
            ->update(['cantidad' => $stock->cantidad + $cantidad, 'updated_at' => now()]);
    } else {
        DB::table('stock')->insert([
            'deposito_id'     => $depositoId,
            'item_id'         => $itemId,
            'cantidad'        => $cantidad,
            'cantidad_minima' => 0,
            'cantidad_maxima' => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}

protected function restarDeStock($depositoId, $itemId, $cantidad)
{
    $stock = DB::table('stock')
        ->where('deposito_id', $depositoId)
        ->where('item_id', $itemId)
        ->first();

    if (!$stock || $stock->cantidad < $cantidad) {
        throw new \Exception("Stock insuficiente para el item ID $itemId.");
    }

    DB::table('stock')
        ->where('deposito_id', $depositoId)
        ->where('item_id', $itemId)
        ->update(['cantidad' => $stock->cantidad - $cantidad, 'updated_at' => now()]);
}
public function update(Request $r, $id)
{
    $nota = NotasVentCab::find($id);

    if (!$nota) {
        return response()->json([
            'mensaje' => 'Nota de venta no encontrada',
            'tipo' => 'error'
        ], 404);
    }

    if ($nota->nota_vent_estado === 'CONFIRMADO') {
        return response()->json([
            'mensaje' => 'No se puede modificar una nota de venta confirmada',
            'tipo' => 'warning'
        ], 409);
    }

    if ($r->nota_vene_condicion_pago === 'CONTADO') {
        $r->merge([
            'nota_vent_intervalo_fecha_vence' => null,
            'nota_vent_cant_cuota' => null
        ]);
    }

    $datos = $r->validate([
        'nota_vent_intervalo_fecha_vence' => 'nullable|date',
        'nota_vent_fecha'                 => 'required|date',
        'nota_vent_estado'                => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
        'nota_vent_cant_cuota'            => 'nullable|integer',
        'nota_vent_tipo'                  => 'required|in:Crédito,Débito',
        'nota_vent_afecta_stock'          => 'nullable|boolean',
        'nota_vent_observaciones'         => ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
        'nota_vene_condicion_pago'        => 'required|in:CONTADO,CRÉDITO',
        'clientes_id'                     => 'required|integer|exists:clientes,id',
        'ventas_cab_id'                   => 'required|integer|exists:ventas_cab,id',
        'empresa_id'                      => 'required|integer|exists:empresa,id',
        'sucursal_id'                     => 'required|integer|exists:sucursal,id',
        'timbrado_id'                     => 'nullable|integer|exists:timbrado,id',
        'nota_vent_nro_comprobante'       => 'nullable|string|max:30',
    ], [
        'nota_vent_estado.in'              => 'El estado no es válido.',
        'nota_vent_tipo.in'                => 'El tipo debe ser Crédito o Débito.',
        'nota_vent_observaciones.not_regex'=> 'Las observaciones contienen caracteres no permitidos.',
        'nota_vent_observaciones.max'      => 'Las observaciones no pueden superar 500 caracteres.',
        'nota_vene_condicion_pago.in'      => 'La condición de pago debe ser CONTADO o CRÉDITO.',
        'clientes_id.exists'               => 'El cliente seleccionado no es válido.',
        'ventas_cab_id.exists'             => 'La venta seleccionada no es válida.',
        'empresa_id.exists'                => 'La empresa seleccionada no es válida.',
        'sucursal_id.exists'               => 'La sucursal seleccionada no es válida.',
    ]);
    $datos['nota_vent_afecta_stock'] = filter_var($r->nota_vent_afecta_stock ?? true, FILTER_VALIDATE_BOOLEAN);

    $nota->update($datos);

    return response()->json([
        'mensaje' => 'Nota de venta modificada con éxito',
        'tipo' => 'success',
        'registro' => $nota
    ], 200);
}
public function anular(Request $r, $id)
{
    $nota = NotasVentCab::find($id);

    if (!$nota) {
        return response()->json([
            'mensaje' => 'Nota de venta no encontrada',
            'tipo' => 'error'
        ], 404);
    }

    if ($nota->nota_vent_estado === 'ANULADO') {
        return response()->json(['mensaje' => 'La nota de venta ya está anulada.', 'tipo' => 'warning'], 409);
    }

    $estadoAnterior = $nota->nota_vent_estado;

    DB::beginTransaction();

    try {
        // ===============================
        // 🔹 ACTUALIZAR CABECERA
        // ===============================
        $nota->nota_vent_estado = 'ANULADO';
        $nota->save();

        // ===============================
        // 🔹 SOLO REVERTIR SI ESTABA CONFIRMADA
        // ===============================
        if ($estadoAnterior === 'CONFIRMADO') {

            $tipoNota = trim($nota->nota_vent_tipo);

            // ===============================
            // 🔹 DETALLES
            // ===============================
            $detalles = NotasVentDet::where('notas_vent_cab_id', $nota->id)
                ->with('tipoImpuesto')
                ->get();

            $totalIVA = 0;

            foreach ($detalles as $det) {
                $subtotal = $det->notas_vent_det_cantidad * $det->notas_vent_det_precio;

                if ($det->tipoImpuesto?->tip_imp_nom === 'IVA10') {
                    $totalIVA += $subtotal / 11;
                } elseif ($det->tipoImpuesto?->tip_imp_nom === 'IVA5') {
                    $totalIVA += $subtotal / 21;
                }

                // ===============================
                // 🔹 STOCK INVERSO (solo si afectó stock)
                // ===============================
                if ($nota->nota_vent_afecta_stock) {
                    if ($tipoNota === 'Débito') {
                        // En confirmar restó → al anular suma
                        $this->agregarAlStock(
                            $det->deposito_id,
                            $det->item_id,
                            $det->notas_vent_det_cantidad
                        );
                    } elseif ($tipoNota === 'Crédito') {
                        // En confirmar sumó → al anular resta
                        $this->restarDeStock(
                            $det->deposito_id,
                            $det->item_id,
                            $det->notas_vent_det_cantidad
                        );
                    }
                }
            }

            // ===============================
            // 🔹 LIBRO DE VENTAS
            // ===============================
            $libro = LibroVentas::where('ventas_cab_id', $nota->ventas_cab_id)->first();

            if ($libro) {
                $libro->libV_monto = $tipoNota === 'Crédito'
                    ? $libro->libV_monto + $totalIVA
                    : max(0, $libro->libV_monto - $totalIVA);

                $libro->save();
            }

            // ===============================
            // 🔹 CUENTAS A COBRAR
            // ===============================
            $cuotas = CtasCobrar::where('ventas_cab_id', $nota->ventas_cab_id)->get();

            foreach ($cuotas as $cuota) {
                $cuota->cta_cob_monto = $tipoNota === 'Crédito'
                    ? $cuota->cta_cob_monto + $totalIVA
                    : max(0, $cuota->cta_cob_monto - $totalIVA);

                $cuota->save();
            }
        }

        // Revertir VentasCab a CONFIRMADO
        $venta = VentasCab::find($nota->ventas_cab_id);
        if ($venta) {
            $venta->vent_estado = 'CONFIRMADO';
            $venta->save();
        }

        DB::commit();

        return response()->json([
            'mensaje' => $estadoAnterior === 'CONFIRMADO'
                ? 'Nota de venta anulada y cambios revertidos correctamente'
                : 'Nota de venta anulada con éxito',
            'tipo' => 'success'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'mensaje' => 'Error al anular nota de venta',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
