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
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_serv_cab_id')->nullable()->after('user_id');
                  ->references('id')->on('orden_serv_cab')
                  ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('contrato_serv_cab', function (Blueprint $table) {
            $table->dropForeign(['orden_serv_cab_id']);
            $table->dropColumn('orden_serv_cab_id');
        });
    }
};
