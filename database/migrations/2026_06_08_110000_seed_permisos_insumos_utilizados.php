<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permisos = [
            'servicios.insumos_utilizados.ver',
            'servicios.insumos_utilizados.crear',
            'servicios.insumos_utilizados.modificar',
            'servicios.insumos_utilizados.anular',
            'servicios.insumos_utilizados.confirmar',
        ];

        foreach ($permisos as $nombre) {
            $existe = DB::table('permisos')->where('per_nombre', $nombre)->exists();
            if (!$existe) {
                DB::table('permisos')->insert([
                    'per_nombre'      => $nombre,
                    'per_descripcion' => $nombre,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permisos')->where('per_nombre', 'LIKE', 'servicios.insumos_utilizados.%')->delete();
    }
};
