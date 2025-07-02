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
            $table->string('code_bar')->nullable()->index(); //codigo de barras
            $table->double('price')->default(0);
            $table->double('price_mayoreo')->default(0); //precio por mayoreo
            $table->double('stock')->default(0);
            $table->double('cantidad_mayoreo')->default(0); //cantidad minima en la cual se toma en cuenta el precio por mayoreo
            $table->double('cantidad_despiezado')->default(0); //cantidad para obtener el precio despiezado
            
            //campos descuentos
            $table->string('tipo_descuento')->nullable(); //monto o porcentaje
            $table->double('monto_porcentaje')->nullable(); //cantidad de monto o porcentaje
            $table->string('vigencia_cantidad_fecha')->nullable(); //si la vigencia del descuento es por fecha o por alguna cantidad
            $table->string('vigencia')->nullable(); //la fecha o cantidad en que vence el decuento

            $table->unsignedBigInteger('unidad_sat_id'); //id de la unidad del sat
            $table->unsignedBigInteger('promotion_id')->nullable(); //id de la promocion (por el momento no se usa)
            $table->boolean('status')->default(1); //status 1 activo - 0 baja
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('unidad_sat_id')->references('id')->on('unidades_sat');
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
