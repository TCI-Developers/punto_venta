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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_proveedor', 100)->nullable();
            $table->string('rfc')->nullable();
            $table->string('phone')->nullable();
            $table->string('contacto')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->integer('credit_days')->default(0);
            $table->double('credit')->default(0);
            $table->double('saldo')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
