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
        Schema::create('devolucion_matriz_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('devolucion_matriz_id');
            $table->double('cantidad');
            $table->string('code_product');
            $table->string('impuesto', 5);
            $table->double('total_impuestos')->default(0);
            $table->double('descuentos')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('total')->default(0);
            $table->foreign('devolucion_matriz_id')->references('id')->on('devoluciones_matriz');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucion_matriz_details');
    }
};
