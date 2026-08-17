<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // cantidad minima de unidades para que aplique precio_mayoreo en vez de precio
            // normal -- ahora la maneja la Matriz igual que el resto de los precios, se
            // cascade despues a parts_to_product.cantidad_mayoreo (ver Controller::cascadePresentationPrices).
            $table->double('cantidad_mayoreo')->default(0)->after('precio_mayoreo');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cantidad_mayoreo');
        });
    }
};
