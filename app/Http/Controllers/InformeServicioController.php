<?php

namespace App\Http\Controllers;

use App\Services\InformeServicioService;
use Illuminate\Http\Request;

class InformeServicioController extends Controller
{
    public function __construct(private InformeServicioService $service) {}

    public function buscar(Request $request)
    {
        $tipos = implode(',', array_keys(config('informes_servicio')));

        $request->validate([
            'tipo'  => "required|in:{$tipos}",
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $resultado = $this->service->ejecutar($request->tipo, $request->all());

        return response()->json($resultado);
    }
}
