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
        Schema::create('sales_detail_cant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_detail_id');
            $table->unsignedBigInteger('part_to_product_id');
            $table->unsignedBigInteger('sale_id');
            $table->integer('cant');
            $table->double('descuento')->default(0);
            $table->double('total_descuento')->default(0);
            $table->boolean('status')->default(1);
            $table->foreign('sale_detail_id')->references('id')->on('sales_detail');
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
        Schema::dropIfExists('sales_detail_cant');
    }
};
