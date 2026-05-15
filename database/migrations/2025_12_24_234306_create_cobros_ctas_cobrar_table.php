<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('cobros_ctas_cobrar', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('cobros_cab_id');
        $table->unsignedBigInteger('ctas_cobrar_id');
        $table->decimal('monto_cobrado', 14, 2);


        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_ctas_cobrar');
    }
};
