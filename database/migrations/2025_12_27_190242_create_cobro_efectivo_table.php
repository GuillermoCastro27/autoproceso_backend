<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cobro_efectivo', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cobros_cab_id');

            $table->decimal('monto_efectivo', 15, 2)
                  ->check('monto_efectivo >= 0');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobro_efectivo');
    }
};

