<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cobro_efectivo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cobros_cab_id')
                  ->constrained('cobros_cab')
                  ->onDelete('cascade');

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

