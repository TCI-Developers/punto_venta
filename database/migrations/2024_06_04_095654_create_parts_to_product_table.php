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
        Schema::create('parts_to_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); //id del producto
            $table->string('code_bar')->nullable(); //codigo de barras
            $table->double('price')->default(0);
            $table->double('stock')->default(0);
            
            //campos descuentos
            $table->string('tipo_descuento')->nullable(); //monto o porcentaje
            $table->double('monto_porcentaje')->nullable(); //cantidad de monto o porcentaje
            $table->string('vigencia_cantidad_fecha')->nullable(); //si la vigencia del descuento es por fecha o por alguna cantidad
            $table->string('vigencia')->nullable(); //la fecha o cantidad en que vence el decuento

            $table->unsignedBigInteger('presentation_product_id'); //id de la presentacion
            $table->unsignedBigInteger('promotion_id')->nullable(); //id de la promocion (por el momento no se usa)
            $table->boolean('status')->default(1); //status 1 activo - 0 baja
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('presentation_product_id')->references('id')->on('presentations_product');
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts_to_product');
    }
};
