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
        Schema::create('detalle_compra_entradas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detalle_compra_id');
            $table->double('entrada')->default(0);
            $table->double('recibido')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('compra_id');
            $table->foreign('detalle_compra_id')->references('id')->on('detalles_compra');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('compra_id')->references('id')->on('compras');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_compra_entradas');
    }
};
