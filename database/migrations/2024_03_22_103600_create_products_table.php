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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description');
            $table->unsignedBigInteger('category_id');
            $table->string('barcode')->nullable();
            $table->string('image_path')->nullable();
            $table->string('unit', 100);
            $table->double('existence');
            $table->boolean('activo')->default(1);
            $table->text('comments')->nullable();
            $table->double('segment_units')->nullable();
            $table->string('unit_presentation');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
