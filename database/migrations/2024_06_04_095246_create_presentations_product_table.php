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
        Schema::create('presentations_product', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('unidad_sat_id');
            $table->boolean('status')->default(1);
            $table->foreign('unidad_sat_id')->references('id')->on('unidades_sat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations_product');
    }
};
