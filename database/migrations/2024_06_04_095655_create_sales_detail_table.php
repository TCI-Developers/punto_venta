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
        Schema::create('sales_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_to_product_id');
            $table->double('amount')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('iva')->default(0);
            $table->double('ieps')->default(0);
            $table->unsignedBigInteger('sale_id');
            $table->double('unit_price')->default(0);
            $table->double('total')->default(0);
            $table->text('notes')->nullable();
            $table->integer('status')->default(1); //1 venta, 2 devolucion
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
        Schema::dropIfExists('sales_detail');
    }
};
