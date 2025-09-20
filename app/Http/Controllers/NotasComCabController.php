<?php

namespace App\Http\Controllers;

use App\Models\NotaCompCab;
use App\Models\NotaCompDet;
use App\Models\CompraCab;
use App\Models\CtasPagar;
use App\Models\Stock;
use App\Models\Deposito;
use App\Models\LibroCompras;
use App\Models\Proveedor;
use App\Models\TipoImpuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class NotasComCabController extends Controller
{
    public function read()
{
    return DB::select("
        SELECT 
    ncc.id,
    p.id AS proveedor_id,
    ncc.empresa_id,
    ncc.sucursal_id,
    ncc.compra_cab_id,
    COALESCE('COMPRA NRO: ' || to_char(cc.id, '0000000'), 'SIN COMPRA') AS compra,
    COALESCE(to_char(ncc.nota_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS nota_comp_intervalo_fecha_vence,
    COALESCE(to_char(ncc.nota_comp_fecha, 'YYYY-MM-DD HH24:MI:SS'), 'N/A') AS nota_comp_fecha,
    ncc.nota_comp_estado,
    COALESCE(ncc.nota_comp_cant_cuota::varchar, '0') AS nota_comp_cant_cuota,
    p.prov_razonsocial AS prov_razonsocial,
    p.prov_ruc AS prov_ruc,
    p.prov_telefono AS prov_telefono,
    p.prov_correo AS prov_correo,
    ncc.nota_comp_tipo,
    ncc.nota_comp_observaciones,
    ncc.nota_comp_condicion_pago,
    u.name AS encargado,
    s.suc_razon_social AS suc_razon_social,
    e.emp_razon_social AS emp_razon_social,
    ncc.created_at,
    ncc.updated_at
FROM 
    notas_comp_cab ncc
JOIN 
    users u ON u.id = ncc.user_id
JOIN 
    sucursal s ON s.empresa_id = ncc.sucursal_id
JOIN 
    empresa e ON e.id = ncc.empresa_id
LEFT JOIN 
    compra_cab cc ON cc.id = ncc.compra_cab_id
LEFT JOIN 
    proveedores p ON p.id = cc.proveedor_id
    ");
}

public function store(Request $r) {
    // Convertir cadenas vacías a null
    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null, 'nota_comp_cant_cuota' => null]);
    }

    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo' => 'required',
        'nota_comp_observaciones' => 'required',
        'user_id' => 'required|integer',
        'compra_cab_id' => 'required|integer', 
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    // Crear cabecera
    $notaCompCab = NotaCompCab::create($datosValidados);

    // Actualizar estado de la compra
    $compracab = CompraCab::find($r->compra_cab_id);
    if ($compracab) {
        $compracab->comp_estado = "PROCESADO";
        $compracab->save();

        // Copiar detalles de compra a nota de compra
        $detalles = DB::table('compra_det')
        ->where('compra_cab_id', $compracab->id)
        ->get();

        foreach ($detalles as $detalle) {
            NotaCompDet::create([
                'notas_comp_cab_id' => $notaCompCab->id,
                'item_id' => $detalle->item_id,
                'tipo_impuesto_id' => $detalle->tipo_impuesto_id,
                'notas_comp_det_cantidad' => $detalle->comp_det_cantidad,
                'notas_comp_det_costo' => $detalle->comp_det_costo,
            ]);
        }
    }

    return response()->json([
        'mensaje' => 'Registro creado con éxito',
        'tipo' => 'success',
        'registro' => $notaCompCab
    ], 201);
}

public function update(Request $r, $id)
{
    $notacompcab = NotaCompCab::find($id);
    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }
     // Convertir cadena vacía a null antes de la validación
    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    // Establecer ord_comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null, 'nota_comp_cant_cuota' => null]);
    }


    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo' => 'required',
        'nota_comp_observaciones' => 'required',
        'user_id' => 'required|integer',
        'compra_cab_id' => 'required|integer', // Cambiado a presupuestos_id
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $datosValidados['nota_comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
        $datosValidados['nota_comp_cant_cuota'] = null; // Establece null si es "CONTADO"
    }

    $notacompcab->update($datosValidados);
    
    return response()->json([
        'mensaje' => 'Registro modificado con éxito',
        'tipo' => 'success',
        'registro' => $notacompcab
    ], 200);
}
public function anular(Request $r, $id)
{
    $notacompcab = NotaCompCab::find($id);

    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Nota de compra no encontrada',
            'tipo'    => 'error',
        ], 404);
    }

    // Guardamos estado previo
    $estadoAnterior = $notacompcab->nota_comp_estado;

    // Validar datos
    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha'                 => 'required|date',
        'nota_comp_estado'                => 'required',
        'nota_comp_cant_cuota'            => 'nullable|integer',
        'nota_comp_tipo'                  => 'required',
        'nota_comp_observaciones'         => 'required',
        'user_id'                         => 'required|integer',
        'compra_cab_id'                   => 'required|integer',
        'empresa_id'                      => 'required|integer',
        'sucursal_id'                     => 'required|integer',
        'nota_comp_condicion_pago'        => 'required|string|max:20'
    ]);

    // Actualizamos cabecera
    $notacompcab->update($datosValidados);
    $notacompcab->nota_comp_estado = "ANULADO";
    $notacompcab->save();

    // Solo revertimos si estaba CONFIRMADO
    if ($estadoAnterior === 'CONFIRMADO') {
        $tipoNota = trim($notacompcab->nota_comp_tipo); // Crédito o Débito

        // Libro de compras
        $libro = LibroCompras::where('compra_cab_id', $notacompcab->compra_cab_id)->first();
        if ($libro) {
            $libro->update([
                'libC_estado' => 'ANULADO',
                'updated_at'  => now()
            ]);
        }

        // Cuentas por pagar
        $ctasPagar = CtasPagar::where('compra_cab_id', $notacompcab->compra_cab_id)->get();
        foreach ($ctasPagar as $cuota) {
            $cuota->update([
                'cta_pag_estado' => 'Anulado',
                'updated_at'     => now()
            ]);
        }

        // Revertir stock y depósito
        $detallesNota = NotaCompDet::where('notas_comp_cab_id', $notacompcab->id)->get();
        foreach ($detallesNota as $detalle) {
            $cantidad = $detalle->notas_comp_det_cantidad;
            $stock    = Stock::where('item_id', $detalle->item_id)->first();
            $deposito = Deposito::where('item_id', $detalle->item_id)->first();

            if ($tipoNota === 'Debito') {
                // En confirmación sumaba → al anular debe restar
                if ($stock && $stock->cantidad >= $cantidad) {
                    $stock->cantidad -= $cantidad;
                    $stock->save();
                } elseif ($deposito && $deposito->cantidad >= $cantidad) {
                    $deposito->cantidad -= $cantidad;
                    $deposito->save();
                }
            } elseif ($tipoNota === 'Crédito') {
                // En confirmación restaba → al anular debe sumar
                if ($stock) {
                    $stock->cantidad += $cantidad;
                    $stock->save();
                } else {
                    Stock::create(['item_id' => $detalle->item_id, 'cantidad' => $cantidad]);
                }
            }
        }
    }

    return response()->json([
        'mensaje' => $estadoAnterior === 'CONFIRMADO'
            ? 'Nota de compra confirmada fue anulada y los cambios revertidos.'
            : 'Nota de compra anulada con éxito.',
        'tipo'    => 'success',
        'registro'=> $notacompcab
    ], 200);
}

    public function confirmar(Request $r, $id) 
{
    $notacompcab = NotaCompCab::find($id);

    if (!$notacompcab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    if ($r->nota_comp_intervalo_fecha_vence === '') {
        $r->merge(['nota_comp_intervalo_fecha_vence' => null]);
    }

    if ($r->nota_comp_condicion_pago === 'CONTADO') {
        $r->merge([
            'nota_comp_intervalo_fecha_vence' => null,
            'nota_comp_cant_cuota' => null
        ]);
    }

    $datosValidados = $r->validate([
        'nota_comp_intervalo_fecha_vence' => 'nullable|date',
        'nota_comp_fecha' => 'required|date',
        'nota_comp_estado' => 'required',
        'nota_comp_cant_cuota' => 'nullable|integer',
        'nota_comp_tipo' => 'required',
        'nota_comp_observaciones' => 'required',
        'user_id' => 'required|integer',
        'compra_cab_id' => 'required|integer',
        'empresa_id' => 'required|integer',
        'sucursal_id' => 'required|integer',
        'nota_comp_condicion_pago' => 'required|string|max:20'
    ]);

    DB::beginTransaction();

    try {
        $notacompcab->nota_comp_estado = 'CONFIRMADO';
        $notacompcab->update($datosValidados);
        $notacompcab->save();

        $tipoNota = trim($notacompcab->nota_comp_tipo); // "Crédito" o "Debito"

        if (in_array($tipoNota, ['Crédito', 'Debito'])) {
            $LibroCompras = LibroCompras::where('compra_cab_id', $notacompcab->compra_cab_id)->first();
            $CtasPagar = CtasPagar::where('compra_cab_id', $notacompcab->compra_cab_id)->first();

            if (!$LibroCompras || !$CtasPagar) {
                return response()->json([
                    'mensaje' => 'Faltan registros relacionados: Libro de compras o Cuenta por pagar no encontrados',
                    'tipo' => 'error'
                ], 404);
            }

            $detalles = NotaCompDet::where('notas_comp_cab_id', $notacompcab->id)->with('tipo_impuesto')->get();
            $totalImpuesto = 0;

            foreach ($detalles as $detalle) {
                $subtotal = $detalle->notas_comp_det_cantidad * $detalle->notas_comp_det_costo;

                if ($detalle->tipo_impuesto) {
                    if ($detalle->tipo_impuesto->tip_imp_nom === 'IVA10') {
                        $totalImpuesto += $subtotal / 11;
                    } elseif ($detalle->tipo_impuesto->tip_imp_nom === 'IVA5') {
                        $totalImpuesto += $subtotal / 21;
                    }
                }

                // Actualiza stock o depósito según tipo de nota
                if ($tipoNota === 'Debito') {
                    $this->agregarAlStockYDeposito($detalle->item_id, $detalle->notas_comp_det_cantidad);
                } elseif ($tipoNota === 'Crédito') {
                    $this->restarDeStockYDeposito($detalle->item_id, $detalle->notas_comp_det_cantidad);
                }
            }

            // Asigna valores directamente calculados desde detalle
            $LibroCompras->libC_tipo_nota = $tipoNota === 'Crédito' ? 'NC' : 'ND';
            $LibroCompras->libC_monto = $totalImpuesto;

            if ($notacompcab->proveedor) {
                $LibroCompras->prov_razonsocial = $notacompcab->proveedor->prov_razonsocial ?? null;
                $LibroCompras->prov_ruc = $notacompcab->proveedor->prov_ruc ?? null;
            }

            $detalleEjemplo = $detalles->first();
            if ($detalleEjemplo && $detalleEjemplo->tipo_impuesto) {
                $LibroCompras->tip_imp_nom = $detalleEjemplo->tipo_impuesto->tip_imp_nom;
            }

            $LibroCompras->save();

            // Ajuste a cuentas a pagar
            $CtasPagar->cta_pag_monto = $tipoNota === 'Crédito'
                ? $CtasPagar->cta_pag_monto + $totalImpuesto
                : max(0, $CtasPagar->cta_pag_monto - $totalImpuesto);

            $CtasPagar->save();
        }

        DB::commit();

        return response()->json([
            'mensaje' => 'Nota de compra confirmada con éxito',
            'tipo' => 'success',
            'registro' => $notacompcab
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al confirmar nota de compra: ' . $e->getMessage(),
            'tipo' => 'error'
        ], 500);
    }
}
protected function agregarAlStockYDeposito($itemId, $cantidad)
{
    $stock = Stock::firstOrCreate(['item_id' => $itemId], ['cantidad' => 0]);
    $deposito = Deposito::firstOrCreate(['item_id' => $itemId], ['cantidad' => 0]);

    $limite = 30;
    $espacio = $limite - $stock->cantidad;

    $aStock = min($cantidad, $espacio);
    $stock->cantidad += $aStock;
    $stock->save();

    $resto = $cantidad - $aStock;
    if ($resto > 0) {
        $deposito->cantidad += $resto;
        $deposito->save();
    }
}

protected function restarDeStockYDeposito($itemId, $cantidad)
{
    $stock = Stock::where('item_id', $itemId)->first();
    $deposito = Deposito::where('item_id', $itemId)->first();

    $restante = $cantidad;

    if ($deposito && $deposito->cantidad > 0) {
        $aRestar = min($deposito->cantidad, $restante);
        $deposito->cantidad -= $aRestar;
        $deposito->save();
        $restante -= $aRestar;
    }

    if ($restante > 0 && $stock && $stock->cantidad > 0) {
        $aRestar = min($stock->cantidad, $restante);
        $stock->cantidad -= $aRestar;
        $stock->save();
        $restante -= $aRestar;
    }

    if ($restante > 0) {
        throw new \Exception("No hay suficiente stock ni en depósito para restar $cantidad unidades del item ID $itemId.");
    }
}
public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            ncc.id,
            TO_CHAR(ncc.nota_comp_fecha, 'dd/mm/yyyy') AS fecha,
            COALESCE(TO_CHAR(ncc.nota_comp_intervalo_fecha_vence, 'dd/mm/yyyy'), 'N/A') AS entrega,
            ncc.nota_comp_tipo AS tipo,
            ncc.nota_comp_observaciones AS observaciones,
            ncc.nota_comp_estado AS estado,
            ncc.nota_comp_condicion_pago AS condicion_pago,
            COALESCE(ncc.nota_comp_cant_cuota::varchar, '0') AS cuotas,
            u.name AS encargado,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            COALESCE(p.prov_razonsocial, 'SIN PROVEEDOR') AS proveedor,
            COALESCE(p.prov_ruc, 'SIN RUC') AS ruc,
            COALESCE('COMPRA NRO: ' || TO_CHAR(cc.id, '0000000'), 'SIN COMPRA') AS compra
        FROM notas_comp_cab ncc
        JOIN users u ON u.id = ncc.user_id
        JOIN sucursal s ON s.empresa_id = ncc.sucursal_id
        JOIN empresa e ON e.id = ncc.empresa_id
        LEFT JOIN compra_cab cc ON cc.id = ncc.compra_cab_id
        LEFT JOIN proveedores p ON p.id = cc.proveedor_id
        WHERE ncc.nota_comp_estado = 'CONFIRMADO'
            AND ncc.nota_comp_fecha BETWEEN ? AND ?
        ORDER BY ncc.nota_comp_fecha ASC
    ", [$desde, $hasta]);
}


}
