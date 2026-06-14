<?php

namespace App\Http\Controllers;

use App\Models\NotaRemiComp;
use App\Models\Timbrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaRemiCompController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                nrc.id,
                TO_CHAR(nrc.nota_remi_fecha, 'dd/mm/yyyy HH24:mi:ss') AS nota_remi_fecha,
                nrc.nota_remi_observaciones,
                nrc.nota_remi_estado,
                nrc.tipo,
                nrc.timbrado_id,
                nrc.nota_remi_nro_comp,
                COALESCE(nrc.nota_remi_nro, '')                   AS nota_remi_nro,
                COALESCE(t.tim_numero, '')                        AS tim_numero,
                COALESCE(TO_CHAR(t.tim_fecha_fin,'DD/MM/YYYY'),'') AS tim_fecha_fin,
                nrc.sucursal_id,
                s.suc_razon_social,
                nrc.proveedor_id,
                COALESCE(prov.prov_razonsocial, '') AS prov_razonsocial,
                COALESCE(prov.prov_ruc, '')         AS prov_ruc,
                COALESCE(prov.prov_telefono, '')    AS prov_telefono,
                COALESCE(nrc.nota_remi_nro, '')     AS nota_remi_nro,
                COALESCE(TO_CHAR(nrc.nota_remi_fecha_emision, 'DD/MM/YYYY'), '') AS nota_remi_fecha_emision,
                nrc.sucursal_destino_id,
                COALESCE(sd.suc_razon_social, '') AS suc_destino_razon_social,
                nrc.empresa_id,
                e.emp_razon_social,
                nrc.funcionario_id,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                nrc.conductor_id,
                COALESCE(cond.fun_nom || ' ' || cond.fun_apellido, '') AS conductor_nombre,
                nrc.tipo_vehiculo_det_id,
                COALESCE(tvd.tv_det_placa,       '') AS tv_det_placa,
                COALESCE(tvd.tv_det_num_chasis,  '') AS tv_det_num_chasis,
                COALESCE(tvd.tv_det_num_motor,   '') AS tv_det_num_motor,
                COALESCE(mtvd.marc_nom,          '') AS tv_marc_nom,
                COALESCE(motvd.modelo_nom,       '') AS tv_modelo_nom,
                nrc.created_at,
                nrc.updated_at
            FROM nota_remi_comp nrc
            JOIN sucursal s        ON s.id    = nrc.sucursal_id
            JOIN empresa e         ON e.id    = nrc.empresa_id
            JOIN funcionario f     ON f.id    = nrc.funcionario_id
            LEFT JOIN sucursal sd          ON sd.id    = nrc.sucursal_destino_id
            LEFT JOIN proveedores prov     ON prov.id  = nrc.proveedor_id
            LEFT JOIN timbrado t           ON t.id     = nrc.timbrado_id
            LEFT JOIN funcionario cond     ON cond.id  = nrc.conductor_id
            LEFT JOIN tipo_vehiculo_det tvd ON tvd.id  = nrc.tipo_vehiculo_det_id
            LEFT JOIN tipo_vehiculo tv_v   ON tv_v.id  = tvd.tipo_vehiculo_id
            LEFT JOIN marca mtvd           ON mtvd.id  = tv_v.marca_id
            LEFT JOIN modelo motvd         ON motvd.id = tv_v.modelo_id
            ORDER BY nrc.id DESC
        ");
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

    public function store(Request $r)
    {
        if ($err = $this->validarFecha($r->nota_remi_fecha)) {
            return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);
        }
        $tipo = $r->tipo ?? 'PROVEEDOR';

        $anioActual = (int) date('Y');
        $esProveedor = $tipo === 'PROVEEDOR';

        $rules = [
            'nota_remi_fecha'        => 'required',
            'nota_remi_observaciones'=> ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'nota_remi_estado'       => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'tipo'                   => 'required|in:PROVEEDOR,TRANSFERENCIA',
            'funcionario_id'         => 'nullable',
            'empresa_id'             => 'required|integer|exists:empresa,id',
            'sucursal_id'            => 'required|integer|exists:sucursal,id',
            'proveedor_id'           => $esProveedor ? 'required|exists:proveedores,id' : 'nullable',
            'nota_remi_nro'          => $esProveedor ? ['required','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'] : 'nullable',
            'nota_remi_fecha_emision'=> $esProveedor ? 'required|date' : 'nullable|date',
            'sucursal_destino_id'    => $tipo === 'TRANSFERENCIA' ? 'required|exists:sucursal,id' : 'nullable',
            'conductor_id'           => $tipo === 'TRANSFERENCIA' ? 'required|exists:funcionario,id' : 'nullable',
            'tipo_vehiculo_det_id'   => $tipo === 'TRANSFERENCIA' ? 'required|exists:tipo_vehiculo_det,id' : 'nullable',
            'tipo_vehiculo'          => $esProveedor ? 'required|in:AUTOMOVIL,MOTOCICLETA' : 'nullable',
            'chofer_nombre'          => $esProveedor ? 'required|string|max:200' : 'nullable|string|max:200',
            'chofer_documento'       => $esProveedor ? ['required','regex:/^\d{6,8}$/'] : 'nullable',
            'chofer_telefono'        => ['nullable','regex:/^09\d{8}$/'],
            'vehiculo_matricula'     => $esProveedor ? 'required|string|max:20' : 'nullable|string|max:20',
            'vehiculo_modelo'        => $esProveedor ? 'required|string|max:100' : 'nullable|string|max:100',
            'vehiculo_color'         => 'nullable|string|max:50',
            'vehiculo_anio'          => ['nullable','digits:4','numeric',"between:1900,{$anioActual}"],
            'vehiculo_nro'           => 'nullable|string|max:50',
        ];

        $messages = [
            'conductor_id.required'   => 'El conductor es obligatorio para transferencias.',
            'conductor_id.exists'     => 'El conductor seleccionado no existe.',
            'tipo_vehiculo_det_id.required' => 'El vehículo es obligatorio para transferencias.',
            'tipo_vehiculo_det_id.exists'   => 'El vehículo seleccionado no existe.',
            'chofer_documento.regex'  => 'La cédula debe tener entre 6 y 8 dígitos numéricos.',
            'chofer_telefono.regex'   => 'El teléfono debe tener el formato 09XXXXXXXX (10 dígitos).',
            'vehiculo_anio.between'   => "El año del vehículo debe estar entre 1900 y {$anioActual}.",
            'vehiculo_anio.digits'    => 'El año debe tener exactamente 4 dígitos.',
            'tipo_vehiculo.in'        => 'El tipo de vehículo debe ser AUTOMOVIL o MOTOCICLETA.',
        ];

        $datosValidados = $r->validate($rules, $messages);
        $datosValidados['funcionario_id'] = auth()->user()->funcionario_id;

        // Validar formato de matrícula según tipo de vehículo
        if ($esProveedor && $r->vehiculo_matricula) {
            $mat = strtoupper(trim($r->vehiculo_matricula));
            if ($r->tipo_vehiculo === 'AUTOMOVIL') {
                if (!preg_match('/^[A-Z]{3,4}\s?\d{3}$/', $mat)) {
                    return response()->json([
                        'mensaje' => 'La matrícula de automóvil debe tener el formato ABC 123 o ABCD 123.',
                        'tipo'    => 'error'
                    ], 422);
                }
            } elseif ($r->tipo_vehiculo === 'MOTOCICLETA') {
                if (!preg_match('/^\d{1,4}\s?[A-Z]{2,3}$/', $mat)) {
                    return response()->json([
                        'mensaje' => 'La matrícula de motocicleta debe tener el formato 123 ABC.',
                        'tipo'    => 'error'
                    ], 422);
                }
            }
        }

        if ($tipo === 'TRANSFERENCIA') {
            if ($r->sucursal_id == $r->sucursal_destino_id) {
                return response()->json([
                    'mensaje' => 'La sucursal de origen y la de destino no pueden ser la misma.',
                    'tipo'    => 'error'
                ], 422);
            }

            $hoy      = now()->toDateString();
            $timbrado = Timbrado::join('tipo_comprobante as tc', 'tc.id', '=', 'timbrado.tipo_comprobante_id')
                ->where('timbrado.empresa_id',  $r->empresa_id)
                ->where('timbrado.sucursal_id', $r->sucursal_id)
                ->where('timbrado.tim_estado',  'activo')
                ->whereDate('timbrado.tim_fecha_inicio', '<=', $hoy)
                ->whereDate('timbrado.tim_fecha_fin',    '>=', $hoy)
                ->where(function($q) {
                    $q->where('tc.tip_comp_abrev', 'NRC')
                      ->orWhereRaw("tc.tip_comp_nombre ILIKE ?", ['%Remisi%Comp%']);
                })
                ->select('timbrado.*')
                ->first();

            if (!$timbrado) {
                return response()->json([
                    'mensaje' => 'No hay timbrado activo de tipo "Nota de Remisión Comp" para esta empresa y sucursal.',
                    'tipo'    => 'error'
                ], 422);
            }

            $nroComp = $timbrado->siguiente();
            $datosValidados['timbrado_id']        = $timbrado->id;
            $datosValidados['nota_remi_nro_comp']  = $nroComp;
            $datosValidados['nota_remi_nro']       = $timbrado->formatearComprobante($nroComp);
        } else {
            $datosValidados['sucursal_destino_id'] = null;
            $datosValidados['timbrado_id']         = null;
            $datosValidados['nota_remi_nro_comp']  = null;
        }

        $notaremicomp = NotaRemiComp::create($datosValidados);
        return response()->json([
            'mensaje'  => 'Registro creado con éxito',
            'tipo'     => 'success',
            'registro' => $notaremicomp
        ], 200);
    }

    public function update(Request $r, $id)
    {
        $notaremicomp = NotaRemiComp::find($id);
        if (!$notaremicomp) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($notaremicomp->nota_remi_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se puede modificar una remisión en estado PENDIENTE.', 'tipo' => 'warning'], 409);
        }

        if ($err = $this->validarFecha($r->nota_remi_fecha)) {
            return response()->json(['mensaje' => $err[0], 'tipo' => 'error'], $err[1]);
        }
        $tipo = $r->tipo ?? $notaremicomp->tipo ?? 'PROVEEDOR';

        $anioActual  = (int) date('Y');
        $esProveedor = $tipo === 'PROVEEDOR';

        $datosValidados = $r->validate([
            'nota_remi_fecha'        => 'required',
            'nota_remi_observaciones'=> ['required', 'string', 'max:500', 'not_regex:/[*<>{}|]/'],
            'nota_remi_estado'       => 'required|in:PENDIENTE,CONFIRMADO,ANULADO',
            'tipo'                   => 'required|in:PROVEEDOR,TRANSFERENCIA',
            'funcionario_id'         => 'nullable',
            'empresa_id'             => 'required|integer|exists:empresa,id',
            'sucursal_id'            => 'required|integer|exists:sucursal,id',
            'proveedor_id'           => $esProveedor ? 'required|exists:proveedores,id' : 'nullable',
            'nota_remi_nro'          => $esProveedor ? ['required','string','max:15','regex:/^\d{3}-\d{3}-\d{7}$/'] : 'nullable',
            'nota_remi_fecha_emision'=> $esProveedor ? 'required|date' : 'nullable|date',
            'sucursal_destino_id'    => $tipo === 'TRANSFERENCIA' ? 'required|exists:sucursal,id' : 'nullable',
            'conductor_id'           => $tipo === 'TRANSFERENCIA' ? 'required|exists:funcionario,id' : 'nullable',
            'tipo_vehiculo_det_id'   => $tipo === 'TRANSFERENCIA' ? 'required|exists:tipo_vehiculo_det,id' : 'nullable',
            'tipo_vehiculo'          => $esProveedor ? 'required|in:AUTOMOVIL,MOTOCICLETA' : 'nullable',
            'chofer_nombre'          => $esProveedor ? 'required|string|max:200' : 'nullable|string|max:200',
            'chofer_documento'       => $esProveedor ? ['required','regex:/^\d{6,8}$/'] : 'nullable',
            'chofer_telefono'        => ['nullable','regex:/^09\d{8}$/'],
            'vehiculo_matricula'     => $esProveedor ? 'required|string|max:20' : 'nullable|string|max:20',
            'vehiculo_modelo'        => $esProveedor ? 'required|string|max:100' : 'nullable|string|max:100',
            'vehiculo_color'         => 'nullable|string|max:50',
            'vehiculo_anio'          => ['nullable','digits:4','numeric',"between:1900,{$anioActual}"],
            'vehiculo_nro'           => 'nullable|string|max:50',
        ], [
            'conductor_id.required'        => 'El conductor es obligatorio para transferencias.',
            'conductor_id.exists'          => 'El conductor seleccionado no existe.',
            'tipo_vehiculo_det_id.required' => 'El vehículo es obligatorio para transferencias.',
            'tipo_vehiculo_det_id.exists'   => 'El vehículo seleccionado no existe.',
            'chofer_documento.regex' => 'La cédula debe tener entre 6 y 8 dígitos numéricos.',
            'chofer_telefono.regex'  => 'El teléfono debe tener el formato 09XXXXXXXX (10 dígitos).',
            'vehiculo_anio.between'  => "El año del vehículo debe estar entre 1900 y {$anioActual}.",
            'vehiculo_anio.digits'   => 'El año debe tener exactamente 4 dígitos.',
            'tipo_vehiculo.in'       => 'El tipo de vehículo debe ser AUTOMOVIL o MOTOCICLETA.',
        ]);

        if ($esProveedor && $r->vehiculo_matricula) {
            $mat = strtoupper(trim($r->vehiculo_matricula));
            if ($r->tipo_vehiculo === 'AUTOMOVIL' && !preg_match('/^[A-Z]{3,4}\s?\d{3}$/', $mat)) {
                return response()->json(['mensaje' => 'Matrícula de automóvil: formato ABC 123 o ABCD 123.', 'tipo' => 'error'], 422);
            }
            if ($r->tipo_vehiculo === 'MOTOCICLETA' && !preg_match('/^\d{1,4}\s?[A-Z]{2,3}$/', $mat)) {
                return response()->json(['mensaje' => 'Matrícula de motocicleta: formato 123 ABC.', 'tipo' => 'error'], 422);
            }
        }

        if ($tipo !== 'TRANSFERENCIA') {
            $datosValidados['sucursal_destino_id'] = null;
        } elseif ($r->sucursal_id == $r->sucursal_destino_id) {
            return response()->json([
                'mensaje' => 'La sucursal de origen y la de destino no pueden ser la misma.',
                'tipo'    => 'error'
            ], 422);
        }

        $notaremicomp->update($datosValidados);
        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $notaremicomp
        ], 200);
    }

    public function anular(Request $r, $id)
    {
        $notaremicomp = NotaRemiComp::find($id);
        if (!$notaremicomp) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($notaremicomp->nota_remi_estado === 'ANULADO') {
            return response()->json(['mensaje' => 'El registro ya está anulado.', 'tipo' => 'error'], 422);
        }

        // Si era TRANSFERENCIA confirmada, revertir el stock
        if ($notaremicomp->nota_remi_estado === 'CONFIRMADO' && $notaremicomp->tipo === 'TRANSFERENCIA') {
            $detalles = DB::table('nota_remi_com_det')
                ->where('nota_remi_comp_id', $id)
                ->get();

            DB::beginTransaction();
            try {
                foreach ($detalles as $det) {
                    // Devolver stock al origen
                    DB::table('stock')
                        ->where('deposito_id', $det->deposito_id)
                        ->where('item_id', $det->item_id)
                        ->increment('cantidad', $det->nota_remi_com_det_cantidad);

                    // Quitar stock del destino
                    DB::table('stock')
                        ->where('deposito_id', $det->deposito_destino_id)
                        ->where('item_id', $det->item_id)
                        ->decrement('cantidad', $det->nota_remi_com_det_cantidad);
                }
                $notaremicomp->update(['nota_remi_estado' => 'ANULADO']);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['mensaje' => 'Error al revertir el stock.', 'tipo' => 'error'], 500);
            }
        } else {
            $notaremicomp->update(['nota_remi_estado' => 'ANULADO']);
        }

        return response()->json([
            'mensaje'  => 'Registro anulado con éxito',
            'tipo'     => 'success',
            'registro' => $notaremicomp
        ], 200);
    }

    public function confirmar(Request $r, $id)
    {
        $notaremicomp = NotaRemiComp::find($id);
        if (!$notaremicomp) {
            return response()->json(['mensaje' => 'Registro no encontrado', 'tipo' => 'error'], 404);
        }

        if ($notaremicomp->nota_remi_estado !== 'PENDIENTE') {
            return response()->json(['mensaje' => 'Solo se pueden confirmar registros en estado PENDIENTE.', 'tipo' => 'error'], 422);
        }

        if ($notaremicomp->tipo === 'TRANSFERENCIA') {
            $detalles = DB::table('nota_remi_com_det')
                ->where('nota_remi_comp_id', $id)
                ->get();

            if ($detalles->isEmpty()) {
                return response()->json(['mensaje' => 'No hay ítems en el detalle para transferir.', 'tipo' => 'error'], 422);
            }

            // Validar que todos los ítems tengan depósito destino
            foreach ($detalles as $det) {
                if (!$det->deposito_destino_id) {
                    $item = DB::table('items')->where('id', $det->item_id)->value('item_decripcion');
                    return response()->json([
                        'mensaje' => "El ítem \"$item\" no tiene depósito destino asignado.",
                        'tipo'    => 'error'
                    ], 422);
                }
            }

            // Validar stock suficiente en origen
            foreach ($detalles as $det) {
                $item = DB::table('items')->where('id', $det->item_id)->value('item_decripcion');
                $stock = DB::table('stock')
                    ->where('deposito_id', $det->deposito_id)
                    ->where('item_id', $det->item_id)
                    ->first();

                if (!$stock || $stock->cantidad < $det->nota_remi_com_det_cantidad) {
                    $disponible = $stock ? $stock->cantidad : 0;
                    return response()->json([
                        'mensaje' => "Stock insuficiente para \"$item\". Disponible: $disponible, requerido: {$det->nota_remi_com_det_cantidad}.",
                        'tipo'    => 'error'
                    ], 422);
                }
            }

            // Mover stock
            DB::beginTransaction();
            try {
                foreach ($detalles as $det) {
                    // Restar del origen
                    DB::table('stock')
                        ->where('deposito_id', $det->deposito_id)
                        ->where('item_id', $det->item_id)
                        ->decrement('cantidad', $det->nota_remi_com_det_cantidad);

                    // Sumar al destino (crear si no existe)
                    $existe = DB::table('stock')
                        ->where('deposito_id', $det->deposito_destino_id)
                        ->where('item_id', $det->item_id)
                        ->exists();

                    if ($existe) {
                        DB::table('stock')
                            ->where('deposito_id', $det->deposito_destino_id)
                            ->where('item_id', $det->item_id)
                            ->increment('cantidad', $det->nota_remi_com_det_cantidad);
                    } else {
                        DB::table('stock')->insert([
                            'deposito_id'      => $det->deposito_destino_id,
                            'item_id'          => $det->item_id,
                            'cantidad'         => $det->nota_remi_com_det_cantidad,
                            'cantidad_minima'  => 0,
                            'cantidad_maxima'  => 0,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }
                $notaremicomp->update(['nota_remi_estado' => 'CONFIRMADO']);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['mensaje' => 'Error al mover el stock: ' . $e->getMessage(), 'tipo' => 'error'], 500);
            }
        } else {
            // PROVEEDOR: solo cambia estado, el stock lo maneja la compra
            $notaremicomp->update(['nota_remi_estado' => 'CONFIRMADO']);
        }

        return response()->json([
            'mensaje'  => 'Registro confirmado con éxito',
            'tipo'     => 'success',
            'registro' => $notaremicomp
        ], 200);
    }

    public function buscarInforme(Request $r)
    {
        $desde = $r->query('desde');
        $hasta = $r->query('hasta');

        return DB::select("
            SELECT
                nrc.id,
                TO_CHAR(nrc.nota_remi_fecha, 'dd/mm/yyyy') AS fecha,
                nrc.nota_remi_observaciones AS observaciones,
                nrc.nota_remi_estado AS estado,
                nrc.tipo,
                f.fun_nom || ' ' || f.fun_apellido AS funcionario,
                s.suc_razon_social AS sucursal,
                COALESCE(sd.suc_razon_social, '') AS suc_destino,
                e.emp_razon_social AS empresa
            FROM nota_remi_comp nrc
            JOIN funcionario f  ON f.id  = nrc.funcionario_id
            JOIN sucursal s     ON s.id  = nrc.sucursal_id
            JOIN empresa e      ON e.id  = nrc.empresa_id
            LEFT JOIN sucursal sd ON sd.id = nrc.sucursal_destino_id
            WHERE nrc.nota_remi_estado = 'CONFIRMADO'
              AND nrc.nota_remi_fecha BETWEEN ? AND ?
            ORDER BY nrc.nota_remi_fecha ASC
        ", [$desde, $hasta]);
    }
}
