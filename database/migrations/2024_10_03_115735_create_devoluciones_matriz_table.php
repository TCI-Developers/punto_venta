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
            $table->unsignedBigInteger('branch_id');
            $table->string('description')->nullable();
            $table->datetime('date');
            $table->boolean('status')->default(1);
            $table->foreign('branch_id')->references('id')->on('branchs');
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
