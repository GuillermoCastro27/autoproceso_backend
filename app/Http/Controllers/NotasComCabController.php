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
public function anular(Request $r, $id){
    $notacompcab = NotaCompCab::find($id);
    if(!$notacompcab){
        return response()->json([
            'mensaje'=>'Registro no encontrado',
            'tipo'=>'error'
        ],404);
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
        'mensaje'=>'Registro anulado con exito',
        'tipo'=>'success',
        'registro'=> $notacompcab
    ],200);
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
        // Actualizar nota de compra
        $notacompcab->nota_comp_estado = 'CONFIRMADO';
        $notacompcab->update($datosValidados);
        $notacompcab->save();

        // Si la condición de pago NO es contado, crear ctas_pagar
        if ($r->nota_comp_condicion_pago !== 'CONTADO') {
            $detalles = NotaCompDet::where('nota_comp_cab_id', $notacompcab->id)->get();

            $totalConImpuesto = 0;

            foreach ($detalles as $detalle) {
                $subtotal = $detalle->not_compd_cant * $detalle->not_compd_costo;
                $impuesto = $subtotal * ($detalle->tipo_impuesto->tasa / 100);
                $totalConImpuesto += $subtotal + $impuesto;
            }

            $cantidadCuotas = $r->nota_comp_cant_cuota ?? 1;
            $intervaloDias = $r->nota_comp_intervalo_fecha_vence ?? 30;

            $montoPorCuota = round($totalConImpuesto / $cantidadCuotas, 2);
            $fechaBase = new \DateTime($r->nota_comp_fecha);

            for ($i = 0; $i < $cantidadCuotas; $i++) {
                $fechaCuota = clone $fechaBase;
                $fechaCuota->modify("+".($intervaloDias * $i)." days");

                CtasPagar::create([
                    'compra_cab_id' => $notacompcab->compra_cab_id,
                    'cta_pag_monto' => $montoPorCuota,
                    'cta_pag_fecha' => $fechaCuota->format('Y-m-d'),
                    'cta_pag_cuota' => $i + 1,
                    'cta_pag_estado' => 'PENDIENTE',
                    'condicion_pago' => $r->nota_comp_condicion_pago
                ]);
            }
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


}
