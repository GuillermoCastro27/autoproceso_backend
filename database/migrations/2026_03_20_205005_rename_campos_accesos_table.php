<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE accesos RENAME COLUMN permisos_id TO permiso_id');
        DB::statement('ALTER TABLE accesos RENAME COLUMN perfiles_id TO perfil_id');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE accesos RENAME COLUMN permiso_id TO permisos_id');
        DB::statement('ALTER TABLE accesos RENAME COLUMN perfil_id TO perfiles_id');
    }
};