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
            $table->unsignedBigInteger('product_id');
            $table->string('code_bar')->nullable();
            $table->double('price')->default(0);
            $table->unsignedBigInteger('presentation_product_id');
            $table->boolean('status')->default(1);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('presentation_product_id')->references('id')->on('presentations_product');
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
