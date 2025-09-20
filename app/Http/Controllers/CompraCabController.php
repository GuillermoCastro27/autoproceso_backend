<?php

namespace App\Http\Controllers;

use App\Models\CompraCab;
use App\Models\OrdenCompraCab; 
use App\Models\CompraDet;
use App\Models\CtasPagar;
use App\Models\LibroCompras;
use App\Models\Stock;
use App\Models\Proveedor;
use App\Models\TipoImpuesto;
use App\Models\Deposito;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraCabController extends Controller
{
    public function read() {
        return DB::select("
            SELECT 
            c.*,
            COALESCE(to_char(c.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS comp_intervalo_fecha_vence,
            c.comp_fecha,
            c.comp_estado,
            COALESCE(c.comp_cant_cuota::varchar, '0') AS comp_cant_cuota,
            c.condicion_pago,
            p.prov_razonsocial,
            p.prov_ruc,
            p.prov_telefono,
            p.prov_correo,
            e.emp_razon_social,
            s.suc_razon_social,
            u.name AS encargado,
            COALESCE('ORDEN DE COMPRA NRO: ' || to_char(occ.id, '0000000'), 'SIN ORDEN DE COMPRA') || 
            COALESCE(' VENCE EL: ' || to_char(occ.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS ordencompra
        FROM 
            compra_cab c
        JOIN 
            proveedores p ON p.id = c.proveedor_id
        JOIN 
            empresa e ON e.id = c.empresa_id
        JOIN 
            sucursal s ON s.empresa_id = c.sucursal_id
        JOIN 
            users u ON u.id = c.user_id
        LEFT JOIN 
            orden_compra_cab occ ON occ.id = c.orden_compra_cab_id;
        ");
    }    
    public function store(Request $r) {
        // Convertir cadena vacía a null antes de la validación
        if ($r->comp_intervalo_fecha_vence === '') {
            $r->merge(['comp_intervalo_fecha_vence' => null]);
        }
    
        // Establecer comp_cant_cuota como null si la condición de pago es "CONTADO"
        if ($r->condicion_pago === 'CONTADO') {
            $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
        }
    
        // Validación de datos
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha' => 'nullable|date',
            'comp_estado' => 'required',
            'comp_cant_cuota' => 'nullable|integer',
            'condicion_pago' => 'required',
            'user_id' => 'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
    
        // Crear la cabecera de la compra
        $compracab = CompraCab::create($datosValidados);
    
        // Obtener la orden de compra cabecera
        $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
        if ($ordencompracab) {
            $ordencompracab->ord_comp_estado = "PROCESADO"; // Cambiar el estado de la orden
            $ordencompracab->save();
    
            // Obtener los detalles de la orden de compra
            $detalles = DB::table('orden_compra_det')
                ->where('orden_compra_cab_id', $ordencompracab->id)
                ->get();
    
            // Insertar los detalles en la tabla compra_det
            foreach ($detalles as $detalle) {
                CompraDet::create([
                    'compra_cab_id' => $compracab->id,
                    'item_id' => $detalle->item_id,
                    'comp_det_cantidad' => $detalle->orden_compra_det_cantidad,
                    'comp_det_costo' => $detalle->orden_compra_det_costo,
                    'tipo_impuesto_id' => $detalle->tipo_impuesto_id,
                ]);
            }
        }
        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $compracab
        ], 201);
    }

    public function update(Request $r, $id){
        $compracab = CompraCab::find($id);
        // Convertir cadena vacía a null antes de la validación
    if ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Establecer   _comp_cant_cuota como null si la condición de pago es "CONTADO"
    if ($r->condicion_pago === 'CONTADO') {
        // Asegurar que estos campos sean null para pagos al contado
        $r->merge(['comp_intervalo_fecha_vence' => null, 'comp_cant_cuota' => null]);
    }
        $datosValidados = $r->validate([
            'comp_intervalo_fecha_vence'=>'nullable|date',
            'comp_fecha'=>'nullable|date',
            'comp_estado'=>'required',
            'comp_cant_cuota'=>'nullable|integer',
            'condicion_pago'=>'required',
            'user_id'=>'required',
            'orden_compra_cab_id'=>'required',
            'proveedor_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        if ($r->condicion_pago === 'CONTADO') {
            $datosValidados['comp_intervalo_fecha_vence'] = null; // Establece null si es "CONTADO"
            $datosValidados['comp_cant_cuota'] = null; // Establece null si es "CONTADO"
        }
        $compracab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $compracab
        ],200);
    }

    public function anular(Request $r, $id) {
    // Buscar el registro por el ID
    $compracab = CompraCab::find($id);

    if (!$compracab) {
        return response()->json([
            'mensaje' => 'Compra no encontrada',
            'tipo'    => 'error',
        ], 404);
    }

    if ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Si la condición de pago es "CONTADO", limpiar campos
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'comp_intervalo_fecha_vence' => null,
            'comp_cant_cuota'            => null
        ]);
    }

    // Validar datos
    $datosValidados = $r->validate([
        'comp_intervalo_fecha_vence' => 'nullable|date',
        'comp_fecha'                 => 'nullable|date',
        'comp_estado'                => 'required',
        'comp_cant_cuota'            => 'nullable|integer',
        'condicion_pago'             => 'required',
        'user_id'                    => 'required',
        'orden_compra_cab_id'        => 'required',
        'proveedor_id'               => 'required',
        'empresa_id'                 => 'required',
        'sucursal_id'                => 'required'
    ]);

    if ($r->condicion_pago === 'CONTADO') {
        $datosValidados['comp_intervalo_fecha_vence'] = null;
        $datosValidados['comp_cant_cuota']            = null;
    }

    // Guardar estado anterior
    $estadoAnterior = $compracab->comp_estado;

    // Actualizar compra a ANULADO
    $compracab->update($datosValidados);
    $compracab->comp_estado = "ANULADO";
    $compracab->save();

    // Inicializar mensaje según estado anterior
    if ($estadoAnterior === 'RECIBIDO') {

        // Libro de compras
        $libro = LibroCompras::where('compra_cab_id', $compracab->id)->first();
        if ($libro) {
            $libro->update([
                'libC_estado' => 'ANULADO',
                'updated_at'  => now()
            ]);
        }

        // Cuentas por pagar
        $ctasPagar = CtasPagar::where('compra_cab_id', $compracab->id)->get();
        foreach ($ctasPagar as $cuota) {
            $cuota->update([
                'cta_pag_estado' => 'Anulado',
                'updated_at'     => now()
            ]);
        }

        // Stock y Depósito
        $detallesCompra = CompraDet::where('compra_cab_id', $compracab->id)->get();
        foreach ($detallesCompra as $detalle) {
            $cantidadAnular = $detalle->comp_det_cantidad;
            $stock = Stock::where('item_id', $detalle->item_id)->first();

            if ($stock) {
                if ($stock->cantidad >= $cantidadAnular) {
                    $stock->cantidad -= $cantidadAnular;
                    $stock->save();
                } else {
                    $restante = $cantidadAnular - $stock->cantidad;
                    $stock->cantidad = 0;
                    $stock->save();

                    $deposito = Deposito::where('item_id', $detalle->item_id)->first();
                    if ($deposito) {
                        $deposito->cantidad = max(0, $deposito->cantidad - $restante);
                        $deposito->save();
                    }
                }
            } else {
                $deposito = Deposito::where('item_id', $detalle->item_id)->first();
                if ($deposito) {
                    $deposito->cantidad = max(0, $deposito->cantidad - $cantidadAnular);
                    $deposito->save();
                }
            }
        }

        $mensaje = 'Registro anulado con éxito. Stock, Depósito, Libro de Compras y Cuentas por Pagar actualizados.';

    } else {
        // Si estaba PENDIENTE
        $mensaje = 'Registro anulado correctamente. La compra estaba pendiente, no se generaron movimientos de Stock, Libro de Compras ni Ctas por Pagar.';
    }

    // Verificar si existe la orden de compra relacionada
    $ordencompracab = OrdenCompraCab::find($r->orden_compra_cab_id);
    if (!$ordencompracab) {
        return response()->json([
            'mensaje' => 'Orden de compra no encontrada',
            'tipo'    => 'error',
        ], 404);
    }

    return response()->json([
        'mensaje' => $mensaje,
        'tipo'    => 'success',
        'registro'=> $compracab
    ], 200);
}

public function calcularTotal(Request $r, $id)
{
    // Verificar si la compra existe
    $compracab = CompraCab::find($id);
    if (!$compracab) {
        return response()->json(['error' => 'Compra no encontrada.'], 404);
    }

    $compraCabId = $id;  // Aquí tomamos el ID de la compra

    // Modificar la consulta para usar bindings y evitar errores de sintaxis
    $detalles = DB::select("
        SELECT 
            cd.comp_det_cantidad AS comp_det_cantidad,
            i.item_costo AS comp_det_costo,
            ti.tip_imp_nom AS tip_imp_nom,
            (cd.comp_det_cantidad * i.item_costo) AS subtotal,
            CASE 
                WHEN ti.tip_imp_nom = 'IVA10' THEN (cd.comp_det_cantidad * i.item_costo) / 11
                WHEN ti.tip_imp_nom = 'IVA5' THEN (cd.comp_det_cantidad * i.item_costo) / 21
                ELSE (cd.comp_det_cantidad * i.item_costo) -- Si no es IVA10 o IVA5, se usa el subtotal sin cambios
            END AS totalConImpuesto
        FROM 
            compra_det cd
        JOIN 
            items i ON cd.item_id = i.id
        JOIN 
            tipo_impuesto ti ON cd.tipo_impuesto_id = ti.id
        WHERE 
            cd.compra_cab_id = :compra_cab_id;", ['compra_cab_id' => $compraCabId]);

    // Variables para almacenar el total general y el total con impuesto
    $totalGral = 0;
    $totalConImpuesto = 0;

    // Recorrer cada detalle y calcular el subtotal e impuestos
    foreach ($detalles as $detalle) {
        $subtotal = $detalle->subtotal; // Subtotal ya calculado
        $totalConImpuestoDetalle = $detalle->totalconimpuesto; // Total con impuestos ya calculado

        // Sumar al total general y total con impuestos
        $totalGral += $subtotal;
        $totalConImpuesto += $totalConImpuestoDetalle;
    }

    // Devolver los resultados como JSON
    return response()->json([
        'totalGral' => number_format($totalGral, 2),
        'totalConImpuesto' => number_format($totalConImpuesto, 2)
    ]);
}

public function confirmar(Request $r, $id) {
    $compracab = CompraCab::find($id);

    if (!$compracab) {
        return response()->json(['error' => 'Compra no encontrada.'], 404);
    }

    // Ajustar valores en función de la condición de pago
    if ($r->condicion_pago === 'CONTADO') {
        $r->merge([
            'comp_intervalo_fecha_vence' => null,
            'comp_cant_cuota' => null
        ]);
    } elseif ($r->comp_intervalo_fecha_vence === '') {
        $r->merge(['comp_intervalo_fecha_vence' => null]);
    }

    // Validaciones y preparación de datos
    $datosValidados = $r->validate([
        'comp_intervalo_fecha_vence' => 'nullable|date',
        'comp_fecha' => 'nullable|date',
        'comp_estado' => 'required',
        'comp_cant_cuota' => 'nullable|integer',
        'condicion_pago' => 'required',
        'user_id' => 'required',
        'orden_compra_cab_id' => 'required',
        'proveedor_id' => 'required',
        'empresa_id' => 'required',
        'sucursal_id' => 'required'
    ]);

    // Actualizar estado de la compra a "RECIBIDO"
    $compracab->update($datosValidados);
    $compracab->comp_estado = "RECIBIDO";
    $compracab->save();

    // Calcular el total con impuestos
    $resultadoCalculo = $this->calcularTotal($r, $id);
    $totalConImpuesto = str_replace(',', '', $resultadoCalculo->getData()->totalConImpuesto);

    // Definir estado y fecha de la siguiente cuota
    $estado = 'Pendiente';
    $fechaCuota = now();

    if ($r->condicion_pago === 'CONTADO') {
        $estado = 'Pagada';
    } elseif ($r->condicion_pago === 'CREDITO' && $r->comp_intervalo_fecha_vence) {
        $fechaCuota = now()->addDays($r->comp_intervalo_fecha_vence);
    }

    // Crear registro en CtasPagar
    CtasPagar::create([
        'compra_cab_id' => $compracab->id,
        'cta_pag_monto' => $totalConImpuesto,
        'cta_pag_fecha' => $fechaCuota,
        'cta_pag_cuota' => $r->comp_cant_cuota ?? 1,
        'cta_pag_estado' => $estado,
        'condicion_pago' => $r->condicion_pago
    ]);

    // Obtener los detalles de compra y actualizar stock y depósito
    $detallesCompra = CompraDet::where('compra_cab_id', $compracab->id)->get();

    foreach ($detallesCompra as $detalle) {
        $stock = Stock::where('item_id', $detalle->item_id)->first();
        $cantidadNueva = $detalle->comp_det_cantidad;

        if ($stock) {
            // Calcular espacio disponible en stock
            $espacioDisponible = 30 - $stock->cantidad;

            if ($cantidadNueva <= $espacioDisponible) {
                // Si cabe en stock, solo sumamos
                $stock->cantidad += $cantidadNueva;
                $stock->save();
            } else {
                // Si supera 30, guardar el excedente en depósito
                $stock->cantidad = 30;
                $stock->save();

                $cantidadExcedente = $cantidadNueva - $espacioDisponible;
                $deposito = Deposito::where('item_id', $detalle->item_id)->first();

                if ($deposito) {
                    // Si el item ya está en depósito, solo actualizamos la cantidad
                    $deposito->cantidad += $cantidadExcedente;
                    $deposito->save();
                } else {
                    // Si no existe en depósito, creamos el registro
                    Deposito::create([
                        'item_id' => $detalle->item_id,
                        'cantidad' => $cantidadExcedente
                    ]);
                }
            }
        } else {
            // Si no hay stock registrado, se crea
            if ($cantidadNueva <= 30) {
                Stock::create([
                    'item_id' => $detalle->item_id,
                    'cantidad' => $cantidadNueva
                ]);
            } else {
                // Si la cantidad supera 30, guardar en stock y el excedente en depósito
                Stock::create([
                    'item_id' => $detalle->item_id,
                    'cantidad' => 30
                ]);

                $cantidadExcedente = $cantidadNueva - 30;
                $deposito = Deposito::where('item_id', $detalle->item_id)->first();

                if ($deposito) {
                    // Si el item ya está en depósito, sumamos la cantidad
                    $deposito->cantidad += $cantidadExcedente;
                    $deposito->save();
                } else {
                    // Si no existe en depósito, lo creamos
                    Deposito::create([
                        'item_id' => $detalle->item_id,
                        'cantidad' => $cantidadExcedente
                    ]);
                }
            }
        }
    }

    // Obtener el tipo de impuesto desde el detalle de compra
    $compraDet = CompraDet::where('compra_cab_id', $compracab->id)->first();

    if (!$compraDet) {
        return response()->json(['error' => 'Detalle de compra no encontrado.'], 404);
    }

    // Obtener datos adicionales del proveedor
    $proveedor = Proveedor::find($r->proveedor_id);
    $provRazonSocial = $proveedor ? $proveedor->prov_razonsocial : null;
    $provRuc = $proveedor ? $proveedor->prov_ruc : null;

    // Obtener nombre del tipo de impuesto
    $tipoImpuestoObj = TipoImpuesto::find($compraDet->tipo_impuesto_id);
    $tipoImpuestoNombre = $tipoImpuestoObj ? $tipoImpuestoObj->tip_imp_nom : null;

    // Insertar el registro en libro_compras con los nuevos campos
        LibroCompras::create([
        'compra_cab_id'   => $compracab->id,
        'libC_monto'      => $totalConImpuesto,
        'libC_fecha'      => now(),
        'libC_cuota'      => $r->comp_cant_cuota ?? 1,
        'libC_tipo_nota'  => $r->libC_tipo_nota,
        'proveedor_id'    => $r->proveedor_id,
        'prov_razonsocial'=> $provRazonSocial,
        'prov_ruc'        => $provRuc,
        'tipo_impuesto_id'=> $compraDet->tipo_impuesto_id,
        'tip_imp_nom'     => $tipoImpuestoNombre,
        'condicion_pago'  => $r->condicion_pago,
        'libC_estado'     => 'ACTIVO',   
        'updated_at'      => now(),
        'created_at'      => now(),
    ]);


    return response()->json([
        'mensaje' => 'Compra registrada con éxito. Cuenta por pagar, Libro de Compras, Stock y Depósito actualizados',
        'tipo' => 'success',
        'registro' => $compracab
    ], 200);
}
public function buscar(Request $r)
{
    $userId = $r->input('user_id'); // Obtener el valor desde la request
    $userName = $r->input('name');  // Obtener el valor del nombre

    return DB::select("
        SELECT 
            cc.id AS compra_cab_id,
            TO_CHAR(cc.comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS comp_fecha,
            COALESCE(to_char(cc.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') AS comp_intervalo_fecha_vence,
            cc.comp_estado,
            cc.condicion_pago,
            COALESCE(cc.comp_cant_cuota::varchar, '0') AS comp_cant_cuota,
            cc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            cc.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            cc.user_id,
            cc.created_at,
            cc.updated_at,
            u.name,
            u.email,
            cc.proveedor_id,
            prov.prov_razonsocial,
            prov.prov_ruc,
            prov.prov_telefono,
            prov.prov_correo,
            'COMPRA NRO: ' || TO_CHAR(cc.id, '0000000') || ' VENCE EL: ' || TO_CHAR(cc.comp_fecha, 'YYYY-MM-DD HH:mm:ss') AS compra,
            COALESCE(to_char(cc.comp_intervalo_fecha_vence, 'YYYY-MM-DD HH:mm:ss'), 'N/A') as nota_comp_intervalo_fecha_vence,
            COALESCE(cc.comp_cant_cuota::varchar, '0') as nota_comp_cantidad_cuota,
            cc.condicion_pago
        FROM 
            compra_cab cc
        JOIN 
            users u ON u.id = cc.user_id
        JOIN 
            sucursal s ON s.empresa_id = cc.sucursal_id
        JOIN 
            empresa e ON e.id = cc.empresa_id
        JOIN 
            proveedores prov ON prov.id = cc.proveedor_id
        WHERE 
            cc.comp_estado = 'RECIBIDO'
        AND 
            cc.user_id = ?
        AND 
            u.name ILIKE ?
    ", [$userId, '%' . $userName . '%']);
}
public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            cc.id,
            TO_CHAR(cc.comp_fecha, 'dd/mm/yyyy') AS fecha,
            CASE 
                WHEN cc.comp_intervalo_fecha_vence IS NOT NULL 
                THEN TO_CHAR(cc.comp_intervalo_fecha_vence, 'dd/mm/yyyy')
                ELSE 'N/A'
            END AS entrega,
            cc.comp_estado AS estado,
            cc.condicion_pago,
            COALESCE(cc.comp_cant_cuota::varchar, '0') AS cuotas,
            u.name AS encargado,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            prov.prov_razonsocial AS proveedor,
            prov.prov_ruc AS ruc,
            'ORDEN DE COMPRA NRO: ' || TO_CHAR(occ.id, '0000000') || 
            CASE 
                WHEN occ.ord_comp_intervalo_fecha_vence IS NOT NULL 
                THEN ' VENCE EL: ' || TO_CHAR(occ.ord_comp_intervalo_fecha_vence, 'YYYY-MM-DD HH24:MI:SS') 
                ELSE ' N/A' 
            END AS ordencompra
        FROM compra_cab cc
        JOIN users u ON u.id = cc.user_id
        JOIN sucursal s ON s.empresa_id = cc.sucursal_id
        JOIN empresa e ON e.id = cc.empresa_id
        LEFT JOIN orden_compra_cab occ ON occ.id = cc.orden_compra_cab_id
        LEFT JOIN presupuestos p ON p.id = occ.presupuesto_id
        LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
        WHERE cc.comp_estado = 'PROCESADO'
            AND cc.comp_fecha BETWEEN ? AND ?
        ORDER BY cc.comp_fecha ASC
    ", [$desde, $hasta]);
}

}