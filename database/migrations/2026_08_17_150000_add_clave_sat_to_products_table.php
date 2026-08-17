<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // clave de producto/servicio del SAT (ej. "50221300") -- la Matriz ya la trae en el
            // catalogo (campo clave_sat), pero nunca se guardaba localmente. Sin esto, la
            // facturacion usaba un codigo generico fijo para TODOS los productos, y el SAT
            // rechaza el timbrado si el Importe de la venta no cae en el rango de precio
            // esperado para ese codigo generico (CFDI40167).
            $table->string('clave_sat', 20)->nullable()->after('unit_description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('clave_sat');
        });
    }
};
