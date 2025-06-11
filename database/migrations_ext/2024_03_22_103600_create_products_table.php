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
            $table->string('code_product', 100)->nullable();
            $table->string('description')->nullable();
            $table->string('barcode')->nullable();
            $table->string('image_path')->nullable();
            $table->string('taxes', 10)->nullable();
            $table->double('amount_taxes')->default(0);
            $table->string('unit', 100)->nullable();
            $table->string('unit_description', 100)->nullable();
            $table->double('existence')->nullable();

            $table->double('precio')->default(0); //precio del producto tal cual se vende normalmente
            $table->double('precio_mayoreo')->default(0); //precio mayoreo
            $table->double('precio_despiece')->default(0); //precio cuando se vende por despiece

            $table->boolean('activo')->default(1);
            $table->text('comments')->nullable();
            $table->double('segment_units')->nullable();
            $table->string('unit_presentation')->nullable();
            // $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('brand_id')->references('id')->on('brands');
            // $table->foreign('branch_id')->references('id')->on('branchs');
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
