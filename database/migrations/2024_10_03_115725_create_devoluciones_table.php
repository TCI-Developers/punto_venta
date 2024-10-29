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
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('part_to_product_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->double('cantidad')->default(0);
            $table->string('description')->nullable();
            $table->date('fecha_devolucion');
            $table->integer('status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('part_to_product_id')->references('id')->on('parts_to_product');
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
