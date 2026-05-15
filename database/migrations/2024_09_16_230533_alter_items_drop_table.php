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
        Schema::table("items", function(Blueprint $table){
            if (Schema::hasColumn('items', 'modelo_id')) $table->dropColumn('modelo_id');
            if (Schema::hasColumn('items', 'marca_id')) $table->dropColumn('marca_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
