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
        Schema::create('cuentas_pagar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('fecha_vencimiento');
            $table->double('subtotal')->default(0);
            $table->double('impuestos')->default(0);
            $table->double('total')->default(0);
            $table->integer('status')->default(1); // 0 = Eliminado logico, 1 = activa, 2 = pagada
            $table->foreign('compra_id')->references('id')->on('compras');
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_pagar');
    }
};
