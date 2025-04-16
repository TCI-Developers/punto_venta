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
        Schema::create('cuentas_pagar_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cxp_id');
            $table->date('date');
            $table->double('importe')->default(0);
            $table->foreign('cxp_id')->references('id')->on('cuentas_pagar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_pagar_detail');
    }
};
