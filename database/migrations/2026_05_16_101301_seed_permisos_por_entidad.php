<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Estructura: modulo => [ entidad => [acciones] ]
        $definicion = [
            'compras' => [
                'pedidos'       => ['ver','crear','modificar','anular','confirmar','eliminar'],
                'presupuestos'  => ['ver','crear','modificar','anular','confirmar','eliminar','aprobar','rechazar'],
                'orden_compra'  => ['ver','crear','modificar','anular','confirmar','eliminar'],
                'registro'      => ['ver','crear','modificar','anular','confirmar'],
                'nota_remision' => ['ver','crear','modificar','anular','confirmar'],
                'ajuste'        => ['ver','crear','modificar','anular','confirmar'],
                'nota_compra'   => ['ver','crear','modificar','anular','confirmar','eliminar'],
            ],
            'ventas' => [
                'pedidos'       => ['ver','crear','modificar','anular','confirmar','eliminar'],
                'ventas'        => ['ver','crear','modificar','anular','confirmar'],
                'nota_remision' => ['ver','crear','modificar','anular','confirmar'],
                'nota_venta'    => ['ver','crear','modificar','anular','confirmar','eliminar'],
            ],
            'servicios' => [
                'solicitud'      => ['ver','crear','modificar','anular','confirmar'],
                'recepcion'      => ['ver','crear','modificar','anular','confirmar'],
                'diagnostico'    => ['ver','crear','modificar','anular','confirmar'],
                'presupuesto'    => ['ver','crear','modificar','anular','confirmar'],
                'orden_servicio' => ['ver','crear','modificar','anular','confirmar'],
                'contrato'       => ['ver','crear','modificar','anular','confirmar'],
                'reclamo'        => ['ver','crear','modificar','anular','procesar','resolver'],
                'promocion'      => ['ver','crear','modificar','anular','confirmar'],
                'descuento'      => ['ver','crear','modificar','anular','confirmar'],
            ],
            'cobros' => [
                'cobro'  => ['ver','crear','modificar','anular','confirmar'],
                'caja'   => ['ver','crear','anular','confirmar'],
                'arqueo' => ['ver','crear','anular','confirmar'],
            ],
            'seguridad' => [
                'usuarios' => ['ver','crear','modificar','eliminar'],
                'permisos' => ['ver','crear','modificar','eliminar'],
                'accesos'  => ['ver','crear','modificar'],
                'modulos'  => ['ver','crear','modificar','eliminar'],
                'perfiles' => ['ver','crear'],
            ],
        ];

        $registros = [];
        foreach ($definicion as $modulo => $entidades) {
            foreach ($entidades as $entidad => $acciones) {
                foreach ($acciones as $accion) {
                    $nombre = $modulo . '.' . $entidad . '.' . $accion;
                    if (!DB::table('permisos')->where('per_nombre', $nombre)->exists()) {
                        $registros[] = [
                            'per_nombre'      => $nombre,
                            'per_descripcion' => ucfirst($accion),
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                }
            }
        }

        if (!empty($registros)) {
            DB::table('permisos')->insert($registros);
        }
    }

    public function down(): void
    {
        // Elimina solo los permisos de 3 niveles (tienen exactamente 2 puntos)
        DB::table('permisos')
            ->whereRaw("LENGTH(per_nombre) - LENGTH(REPLACE(per_nombre, '.', '')) = 2")
            ->delete();
    }
};
