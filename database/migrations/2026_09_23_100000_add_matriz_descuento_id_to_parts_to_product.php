<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts_to_product', function (Blueprint $table) {
            $table->unsignedBigInteger('matriz_descuento_id')->nullable()->after('vigencia');
        });
    }

    public function down(): void
    {
        Schema::table('parts_to_product', function (Blueprint $table) {
            $table->dropColumn('matriz_descuento_id');
        });
    }
};
