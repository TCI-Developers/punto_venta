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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('folio');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('user_id');
            $table->date('programacion_entrega')->nullable();
            $table->date('fecha_recibido')->nullable();
            $table->integer('plazo')->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->char('moneda', 3);
            $table->string('tipo');
            $table->double('importe')->default(0);
            $table->double('impuesto_productos')->default(0);
            $table->double('descuentos')->default(0);
            $table->double('subtotal')->default(0);
            $table->double('total')->default(0);
            $table->string('observaciones')->nullable();
            $table->integer('status')->default(1); //0 = Cancelada - 1 = pendiente - 2 = Autorizada - 3 = Solicitado - 4 = Recibido
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
