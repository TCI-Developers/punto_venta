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
        Schema::create('detalles_compra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('code_product');
            $table->string('descripcion_producto');
            $table->string('taxes', 10)->nullable();
            $table->float('amount_taxes')->default(0);
            $table->double('precio_unitario')->default(0);
            $table->double('precio_mayoreo')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('impuestos')->default(0);
            $table->double('descuentos')->default(0);
            $table->double('total')->default(0);
            $table->boolean('status')->default(1);
            $table->foreign('compra_id')->references('id')->on('compras');
            $table->foreign('producto_id')->references('id')->on('products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_compra');
    }
};
