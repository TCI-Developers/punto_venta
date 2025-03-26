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
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('producto_id');
            $table->string('descripción_producto');
            $table->double('entrada')->default(0);
            $table->float('iva')->default(0);
            $table->float('ieps')->default(0);
            $table->float('isr')->default(0);
            $table->double('precio_unitario')->default(0);
            $table->double('precio_mayoreo')->default(0);
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
