<?php

namespace App\Http\Controllers;

use App\Services\InformeVentasService;
use Illuminate\Http\Request;

class InformeVentasController extends Controller
{
    public function __construct(private InformeVentasService $service) {}

    public function buscar(Request $request)
    {
        $tipos = implode(',', array_keys(config('informes_ventas')));
        $request->validate([
            'tipo'  => "required|in:{$tipos}",
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        return response()->json($this->service->ejecutar($request->tipo, $request->all()));
    }
}
