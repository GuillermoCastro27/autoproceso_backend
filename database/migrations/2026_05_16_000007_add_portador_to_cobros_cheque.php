<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {
            if (!Schema::hasColumn('cobros_cheque', 'portador')) {
                $table->string('portador', 100)->nullable()->after('monto_cheque');
            }
            if (!Schema::hasColumn('cobros_cheque', 'fecha_cobro_diferido')) {
                $table->timestamp('fecha_cobro_diferido')->nullable()->after('portador');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cobros_cheque', function (Blueprint $table) {
            $table->dropColumnIfExists('portador');
            $table->dropColumnIfExists('fecha_cobro_diferido');
        });
    }
};
