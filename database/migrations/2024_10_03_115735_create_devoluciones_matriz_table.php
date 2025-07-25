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
        Schema::create('devoluciones_matriz', function (Blueprint $table) {
            $table->id();
            $table->string('driver');
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');
            $table->double('cantidad');
            $table->double('impuesto')->default(0);
            $table->double('total_impuesto')->default(0);
            $table->double('descuento')->default(0);
            $table->double('total_descuentos')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('total')->default(0);
            $table->string('description')->nullable();
            $table->datetime('date');
            $table->boolean('status')->default(1);
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('compra_id')->references('id')->on('compras');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones_matriz');
    }
};
