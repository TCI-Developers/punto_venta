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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('folio');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('uuid')->nullable();
            $table->unsignedBigInteger('payment_method_id');
            $table->string('type_payment', 50);
            $table->double('amount_received')->default(0);
            $table->double('change')->default(0);
            $table->string('sat_document_type')->nullable();
            $table->double('total_sale')->default(0);
            $table->string('coin', 10)->default('MXN');
            $table->integer('status')->default(1); //1 activa, 0 cancelada, 2 cerrada
            $table->unsignedBigInteger('customer_id');
            $table->string('type', 100)->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
