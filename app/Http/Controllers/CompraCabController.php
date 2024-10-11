<?php

namespace App\Http\Controllers;

use App\Models\CompraCab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompraCabController extends Controller
{
    // Listar todas las compras
    public function read()
    {
        return CompraCab::all();
    }

    // Crear una nueva compra
    public function store(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha' => 'required|date',
            'comp_estado' => 'required|string|max:50',
            'comp_cant_cuota' => 'nullable|string',
            'condicion_pago' => 'nullable|string|max:20',
            'user_id' => 'required|exists:users,id',
            'orden_compra_cab_id' => 'required|exists:orden_compra_cab,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'empresa_id' => 'required|exists:empresa,id',
            'sucursal_id' => 'required|exists:sucursal,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear la compra
        $compra = CompraCab::create($request->all());
        return response()->json(['message' => 'Compra creada exitosamente', 'compra' => $compra], 201);
    }

    // Modificar una compra
    public function update(Request $request, $id)
    {
        // Buscar la compra
        $compra = CompraCab::find($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        // Validar los datos
        $validator = Validator::make($request->all(), [
            'comp_intervalo_fecha_vence' => 'nullable|date',
            'comp_fecha' => 'required|date',
            'comp_estado' => 'required|string|max:50',
            'comp_cant_cuota' => 'nullable|string',
            'condicion_pago' => 'nullable|string|max:20',
            'user_id' => 'required|exists:users,id',
            'orden_compra_cab_id' => 'required|exists:orden_compra_cab,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'empresa_id' => 'required|exists:empresa,id',
            'sucursal_id' => 'required|exists:sucursal,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Actualizar la compra
        $compra->update($request->all());
        return response()->json(['message' => 'Compra actualizada exitosamente', 'compra' => $compra], 200);
    }

    // Anular una compra
    public function anular($id)
    {
        // Buscar la compra
        $compra = CompraCab::find($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        // Cambiar el estado a ANULADO
        $compra->comp_estado = 'ANULADO';
        $compra->save();

        return response()->json(['message' => 'Compra anulada exitosamente', 'compra' => $compra], 200);
    }

    // Confirmar una compra
    public function confirmar($id)
    {
        // Buscar la compra
        $compra = CompraCab::find($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        // Cambiar el estado a CONFIRMADO
        if ($compra->comp_estado == 'PENDIENTE') {
            $compra->comp_estado = 'CONFIRMADO';
            $compra->save();
            return response()->json(['message' => 'Compra confirmada exitosamente', 'compra' => $compra], 200);
        }

        return response()->json(['message' => 'Solo se pueden confirmar compras en estado PENDIENTE'], 400);
    }
}