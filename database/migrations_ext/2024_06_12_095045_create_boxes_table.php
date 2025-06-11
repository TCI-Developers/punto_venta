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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // $table->unsignedBigInteger('branch_id');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->double('amount_credit_user')->default(0);
            $table->double('amount_credit_system')->default(0);
            $table->double('amount_cash_user')->default(0);
            $table->double('amount_cash_system')->default(0);
            $table->double('start_amount_box')->default(0);
            $table->double('total_user')->default(0);
            $table->double('total_system')->default(0);

            $table->float('ticket_1000')->default(0);
            $table->float('ticket_500')->default(0);
            $table->float('ticket_200')->default(0);
            $table->float('ticket_100')->default(0);
            $table->float('ticket_50')->default(0);
            $table->float('ticket_20')->default(0);

            $table->float('coin_20')->default(0);
            $table->float('coin_10')->default(0);
            $table->float('coin_5')->default(0);
            $table->float('coin_2')->default(0);
            $table->float('coin_1')->default(0);
            $table->float('coin_50_cen')->default(0);

            $table->integer('status')->default(1); //1 = Correcto, 2 = No coincide lo ingresado con lo del sistema, 3 = corregido
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('branch_id')->references('id')->on('branchs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
