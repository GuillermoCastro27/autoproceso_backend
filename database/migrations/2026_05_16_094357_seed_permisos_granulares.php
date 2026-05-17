<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            // compras
            ['per_nombre' => 'compras.ver',           'per_descripcion' => 'Ver'],
            ['per_nombre' => 'compras.crear',          'per_descripcion' => 'Crear'],
            ['per_nombre' => 'compras.modificar',      'per_descripcion' => 'Modificar'],
            ['per_nombre' => 'compras.anular',         'per_descripcion' => 'Anular'],
            ['per_nombre' => 'compras.confirmar',      'per_descripcion' => 'Confirmar'],
            ['per_nombre' => 'compras.eliminar',       'per_descripcion' => 'Eliminar'],
            ['per_nombre' => 'compras.aprobar',        'per_descripcion' => 'Aprobar'],
            ['per_nombre' => 'compras.rechazar',       'per_descripcion' => 'Rechazar'],
            // ventas
            ['per_nombre' => 'ventas.ver',             'per_descripcion' => 'Ver'],
            ['per_nombre' => 'ventas.crear',           'per_descripcion' => 'Crear'],
            ['per_nombre' => 'ventas.modificar',       'per_descripcion' => 'Modificar'],
            ['per_nombre' => 'ventas.anular',          'per_descripcion' => 'Anular'],
            ['per_nombre' => 'ventas.confirmar',       'per_descripcion' => 'Confirmar'],
            ['per_nombre' => 'ventas.eliminar',        'per_descripcion' => 'Eliminar'],
            // servicios
            ['per_nombre' => 'servicios.ver',          'per_descripcion' => 'Ver'],
            ['per_nombre' => 'servicios.crear',        'per_descripcion' => 'Crear'],
            ['per_nombre' => 'servicios.modificar',    'per_descripcion' => 'Modificar'],
            ['per_nombre' => 'servicios.anular',       'per_descripcion' => 'Anular'],
            ['per_nombre' => 'servicios.confirmar',    'per_descripcion' => 'Confirmar'],
            ['per_nombre' => 'servicios.eliminar',     'per_descripcion' => 'Eliminar'],
            ['per_nombre' => 'servicios.procesar',     'per_descripcion' => 'Procesar'],
            ['per_nombre' => 'servicios.resolver',     'per_descripcion' => 'Resolver'],
            // cobros
            ['per_nombre' => 'cobros.ver',             'per_descripcion' => 'Ver'],
            ['per_nombre' => 'cobros.crear',           'per_descripcion' => 'Crear'],
            ['per_nombre' => 'cobros.modificar',       'per_descripcion' => 'Modificar'],
            ['per_nombre' => 'cobros.anular',          'per_descripcion' => 'Anular'],
            ['per_nombre' => 'cobros.confirmar',       'per_descripcion' => 'Confirmar'],
            // referenciales
            ['per_nombre' => 'referenciales.ver',      'per_descripcion' => 'Ver'],
            ['per_nombre' => 'referenciales.crear',    'per_descripcion' => 'Crear'],
            ['per_nombre' => 'referenciales.modificar','per_descripcion' => 'Modificar'],
            ['per_nombre' => 'referenciales.eliminar', 'per_descripcion' => 'Eliminar'],
            // seguridad
            ['per_nombre' => 'seguridad.ver',          'per_descripcion' => 'Ver'],
            ['per_nombre' => 'seguridad.crear',        'per_descripcion' => 'Crear'],
            ['per_nombre' => 'seguridad.modificar',    'per_descripcion' => 'Modificar'],
            ['per_nombre' => 'seguridad.eliminar',     'per_descripcion' => 'Eliminar'],
        ];

        foreach ($permisos as $p) {
            if (!DB::table('permisos')->where('per_nombre', $p['per_nombre'])->exists()) {
                DB::table('permisos')->insert(array_merge($p, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('permisos')->where('per_nombre', 'LIKE', '%.%')->delete();
    }
};
