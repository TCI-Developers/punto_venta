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
        Schema::create('empresa_details', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social')->nullable();
            $table->string('name');
            $table->string('rfc')->nullable();
            $table->string('address');
            $table->string('vigencia');
            $table->string('path_logo')->nullable();
             $table->unsignedBigInteger('branch_id')->nullable();
             $table->foreign('branch_id')->references('id')->on('branchs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_details');
    }
};
