<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AjusteCab;
use App\Models\AjusteDet;
use App\Models\Stock;

class AjusteCabController extends Controller
{
    public function read() {
        return DB::select("SELECT 
            ac.id,
            TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS ajus_cab_fecha,
            ac.ajus_cab_estado,
            ac.tipo_ajuste,
            ac.sucursal_id,
            s.suc_razon_social AS suc_razon_social,
            ac.empresa_id,
            e.emp_razon_social AS emp_razon_social,
            ac.funcionario_id,
            ac.motivo_ajuste_id,
            ma.descripcion AS descripcion,
            ac.created_at,
            ac.updated_at,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario
        FROM ajuste_cab ac
        JOIN sucursal s ON s.id = ac.sucursal_id
        JOIN empresa e ON e.id = ac.empresa_id
        JOIN funcionario f ON f.id = ac.funcionario_id
        JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id;");
    }

    private function validarFecha(string $fecha): ?array
    {
        if (!$fecha) return ['La fecha es obligatoria.', 422];
        $dt = \DateTime::createFromFormat('d/m/Y H:i:s', $fecha);
        if (!$dt) return ['El formato de fecha es inválido. Use DD/MM/YYYY HH:mm:ss.', 422];
        $hoy = (new \DateTime())->format('d/m/Y');
        $fechaDia = $dt->format('d/m/Y');
        if ($fechaDia !== $hoy) {
            return ['La fecha debe ser la de hoy (' . $hoy . ').', 422];
        }
        return null;
    }

    public function store(Request $r){
        if ($err = $this->validarFecha($r->ajus_cab_fecha)) {
            return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);
        }

        $datosValidados = $r->validate([
            'ajus_cab_fecha'   => 'required',
            'ajus_cab_estado'  => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'tipo_ajuste'      => 'required|in:Entrada,Salida',
            'funcionario_id'   => 'nullable',
            'empresa_id'       => 'required|integer|exists:empresa,id',
            'sucursal_id'      => 'required|integer|exists:sucursal,id',
            'motivo_ajuste_id' => 'required|integer|exists:motivo_ajuste,id',
        ], [
            'ajus_cab_estado.in' => 'El estado debe ser PENDIENTE, CONFIRMADO o ANULADO.',
            'tipo_ajuste.in'     => 'El tipo de ajuste debe ser Entrada o Salida.',
        ]);

        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;
        $ajustecab = AjusteCab::create($datosValidados);

        return response()->json([
            'mensaje' => 'Registro creado con éxito',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
    }

    public function update(Request $r, $id){
        if ($err = $this->validarFecha($r->ajus_cab_fecha)) {
            return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);
        }
        $ajustecab = AjusteCab::find($id);
        if (!$ajustecab) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($ajustecab->ajus_cab_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede modificar un ajuste en estado PENDIENTE.', 'tipo' => 'warning'], 409);
        }

        $datosValidados = $r->validate([
            'ajus_cab_fecha'   => 'required',
            'ajus_cab_estado'  => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'tipo_ajuste'      => 'required|in:Entrada,Salida',
            'empresa_id'       => 'required|integer|exists:empresa,id',
            'sucursal_id'      => 'required|integer|exists:sucursal,id',
            'motivo_ajuste_id' => 'required|integer|exists:motivo_ajuste,id',
        ], [
            'ajus_cab_estado.in' => 'El estado debe ser PENDIENTE, CONFIRMADO o ANULADO.',
            'tipo_ajuste.in'     => 'El tipo de ajuste debe ser Entrada o Salida.',
        ]);

        $ajustecab->update($datosValidados);

        return response()->json([
            'mensaje' => 'Registro modificado con éxito',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);
    }

    public function anular(Request $r, $id)
{
    $ajustecab = AjusteCab::find($id);
    if (!$ajustecab) {
        return response()->json([
            'mensaje' => 'Registro no encontrado',
            'tipo' => 'error'
        ], 404);
    }

    if ($ajustecab->ajus_cab_estado === 'ANULADO') {
        return response()->json(['mensaje' => 'El ajuste ya está anulado.', 'tipo' => 'warning'], 409);
    }

    $estadoAnterior = $ajustecab->ajus_cab_estado;
    $ajustecab->ajus_cab_estado = 'ANULADO';
    $ajustecab->save();

    // Si el ajuste estaba CONFIRMADO → revertimos el stock
    if ($estadoAnterior === 'CONFIRMADO') {
        $ajusteDetalles = AjusteDet::where('ajuste_cab_id', $ajustecab->id)->get();

        foreach ($ajusteDetalles as $detalle) {
            $stock = Stock::where('item_id', $detalle->item_id)
                ->where('deposito_id', $detalle->deposito_id)
                ->first();

            if ($stock) {
                if ($ajustecab->tipo_ajuste === 'Entrada') {
                    if ($stock->cantidad < $detalle->ajus_det_cantidad) {
                        return response()->json([
                            'mensaje' => "No se puede anular. El stock del ítem {$detalle->item_id} es insuficiente para revertir la entrada.",
                            'tipo' => 'error'
                        ], 400);
                    }
                    DB::table('stock')
                        ->where('item_id', $detalle->item_id)
                        ->where('deposito_id', $detalle->deposito_id)
                        ->update(['cantidad' => $stock->cantidad - $detalle->ajus_det_cantidad, 'updated_at' => now()]);
                } elseif ($ajustecab->tipo_ajuste === 'Salida') {
                    DB::table('stock')
                        ->where('item_id', $detalle->item_id)
                        ->where('deposito_id', $detalle->deposito_id)
                        ->update(['cantidad' => $stock->cantidad + $detalle->ajus_det_cantidad, 'updated_at' => now()]);
                }
            }
        }
    }

    return response()->json([
        'mensaje' => $estadoAnterior === 'CONFIRMADO'
            ? 'Ajuste confirmado anulado con éxito y stock revertido.'
            : 'Ajuste pendiente anulado con éxito.',
        'tipo' => 'success',
        'registro' => $ajustecab
    ], 200);
}


    public function confirmar(Request $r, $id) {
    \DB::beginTransaction();
    try {
        // Buscar cabecera
        $ajustecab = AjusteCab::find($id);
        if (!$ajustecab) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo' => 'error'
            ], 404);
        }

        if ($ajustecab->ajus_cab_estado !== 'PENDIENTE') {
            return response()->json([
                'mensaje' => 'Solo se puede confirmar un ajuste en estado PENDIENTE.',
                'tipo'    => 'warning'
            ], 409);
        }

        // Obtener los detalles del ajuste
        $ajusteDetalles = AjusteDet::where('ajuste_cab_id', $ajustecab->id)->get();

        if ($ajusteDetalles->isEmpty()) {
            return response()->json([
                'mensaje' => 'No se encontraron detalles para este ajuste.',
                'tipo' => 'error'
            ], 400);
        }

        // Recorremos y aplicamos los cambios al stock según tipo_ajuste
        foreach ($ajusteDetalles as $detalle) {
            // cantidad en detalle se espera positiva
            $cantidad = (float) $detalle->ajus_det_cantidad;
            if ($cantidad <= 0) {
                return response()->json([
                    'mensaje' => 'Cantidad inválida en detalle para el item: ' . $detalle->item_id,
                    'tipo' => 'error'
                ], 400);
            }

            if (!$detalle->deposito_id) {
                return response()->json([
                    'mensaje' => 'El ítem ' . $detalle->item_id . ' no tiene depósito asignado en el detalle.',
                    'tipo'    => 'error'
                ], 400);
            }

            $stock = Stock::where('item_id', $detalle->item_id)
                ->where('deposito_id', $detalle->deposito_id)
                ->first();

            if (!$stock) {
                if ($ajustecab->tipo_ajuste === 'Entrada') {
                    // Crear el registro de stock si no existe (entrada de nuevo ítem)
                    DB::table('stock')->insert([
                        'deposito_id'     => $detalle->deposito_id,
                        'item_id'         => $detalle->item_id,
                        'cantidad'        => $cantidad,
                        'cantidad_minima' => 0,
                        'cantidad_maxima' => 0,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                    continue;
                }
                return response()->json([
                    'mensaje' => 'No hay stock del ítem ' . $detalle->item_id . ' en el depósito indicado para realizar la salida.',
                    'tipo' => 'error'
                ], 400);
            }

            if ($ajustecab->tipo_ajuste === 'Entrada') {
                $nuevaCantidad = $stock->cantidad + $cantidad;

                if ($stock->cantidad_maxima > 0 && $nuevaCantidad > $stock->cantidad_maxima) {
                    return response()->json([
                        'mensaje' => "La cantidad en stock no puede superar el máximo de {$stock->cantidad_maxima} para el ítem {$detalle->item_id}.",
                        'tipo' => 'error'
                    ], 400);
                }

                DB::table('stock')
                    ->where('item_id', $detalle->item_id)
                    ->where('deposito_id', $detalle->deposito_id)
                    ->update(['cantidad' => $nuevaCantidad, 'updated_at' => now()]);
            }
            elseif ($ajustecab->tipo_ajuste === 'Salida') {
                if ($stock->cantidad < $cantidad) {
                    return response()->json([
                        'mensaje' => 'Stock insuficiente para el ítem ' . $detalle->item_id . '. Disponible: ' . $stock->cantidad . ', requerido: ' . $cantidad . '.',
                        'tipo' => 'error'
                    ], 400);
                }

                DB::table('stock')
                    ->where('item_id', $detalle->item_id)
                    ->where('deposito_id', $detalle->deposito_id)
                    ->update(['cantidad' => $stock->cantidad - $cantidad, 'updated_at' => now()]);
            }
            else {
                return response()->json([
                    'mensaje' => 'Tipo de ajuste desconocido: ' . $ajustecab->tipo_ajuste,
                    'tipo' => 'error'
                ], 400);
            }
        }

        // Finalmente marcamos la cabecera como CONFIRMADO (independientemente de lo que vino en la request)
        $ajustecab->ajus_cab_estado = 'CONFIRMADO';
        $ajustecab->save();

        \DB::commit();
        return response()->json([
            'mensaje' => 'Ajuste confirmado con éxito y stock actualizado.',
            'tipo' => 'success',
            'registro' => $ajustecab
        ], 200);

    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'mensaje' => 'Error al confirmar el ajuste de inventario: ' . $e->getMessage(),
            'tipo' => 'error'
        ], 500);
    }
}
        
    public function buscarInforme(Request $r)
{
    $desde = $r->query('desde');
    $hasta = $r->query('hasta');

    return DB::select("
        SELECT 
            ac.id,
            TO_CHAR(ac.ajus_cab_fecha, 'dd/mm/yyyy') AS fecha,
            ac.ajus_cab_estado AS estado,
            ac.tipo_ajuste AS tipo,
            f.fun_nom || ' ' || f.fun_apellido AS funcionario,
            s.suc_razon_social AS sucursal,
            e.emp_razon_social AS empresa,
            ma.descripcion AS motivo
        FROM ajuste_cab ac
        JOIN funcionario f ON f.id = ac.funcionario_id
        JOIN sucursal s ON s.id = ac.sucursal_id
        JOIN empresa e ON e.id = ac.empresa_id
        JOIN motivo_ajuste ma ON ma.id = ac.motivo_ajuste_id
        WHERE ac.ajus_cab_estado = 'CONFIRMADO'
        AND ac.ajus_cab_fecha BETWEEN ? AND ?
        ORDER BY ac.ajus_cab_fecha ASC
    ", [$desde, $hasta]);
}
}
