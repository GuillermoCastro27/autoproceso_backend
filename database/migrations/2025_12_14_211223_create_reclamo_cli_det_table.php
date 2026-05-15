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
        Schema::create('reclamo_cli_det', function (Blueprint $table) {
            $table->unsignedBigInteger('reclamo_cli_cab_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tipo_impuesto_id');
            $table->decimal('rec_cli_det_cantidad', 14, 2);
            $table->decimal('rec_cli_det_costo', 14, 2);
            $table->integer('rec_cli_det_cantidad_stock');
            $table->primary(['reclamo_cli_cab_id', 'item_id']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamo_cli_det');
    }
};
