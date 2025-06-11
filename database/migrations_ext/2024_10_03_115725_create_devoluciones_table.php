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
            $table->unsignedBigInteger('sale_id'); //id venta
            $table->unsignedBigInteger('branch_id'); //id sucursal
            $table->unsignedBigInteger('user_dev'); //id usuario qeu creo la devolucion
            $table->double('cantidad')->default(0);
            $table->string('description')->nullable();
            $table->date('fecha_devolucion');
            $table->double('total_descuentos')->default(0);
            $table->double('total_devolucion')->default(0);
            $table->integer('status')->default(1);
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->foreign('user_dev')->references('id')->on('users');
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
