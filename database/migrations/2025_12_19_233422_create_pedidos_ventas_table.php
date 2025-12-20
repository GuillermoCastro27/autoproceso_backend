<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_ventas', function (Blueprint $table) {
            $table->id();

            // 📅 Fechas
            $table->timestamp('ped_ven_fecha');       // fecha del pedido
            $table->timestamp('ped_ven_vence');       // fecha de vencimiento

            // 📝 Observaciones
            $table->string('ped_ven_observaciones', 200)->nullable();

            // 📌 Estado
            $table->string('ped_ven_estado', 50);

            // 🔗 Relaciones
            $table->unsignedBigInteger('clientes_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('user_id');

            // 🔑 Foreign Keys
            $table->foreign('clientes_id')
                  ->references('id')
                  ->on('clientes')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresa')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('sucursal_id')
                  ->references('empresa_id')
                  ->on('sucursal')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_ventas');
    }
};
