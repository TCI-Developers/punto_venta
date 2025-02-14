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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('description')->nullable();
            $table->double('cantidad_producto'); //3 productos
            $table->double('cantidad_productos_a_pagar'); //paga solo 2
            $table->double('vigencia_cantidad')->nullable(); //limite de productos para vender
            $table->date('vigencia_fecha')->nullable();
            $table->integer('status')->default(1); //0 = Inhabilitado, 2 = habilitado, 3 = Finalizado
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
