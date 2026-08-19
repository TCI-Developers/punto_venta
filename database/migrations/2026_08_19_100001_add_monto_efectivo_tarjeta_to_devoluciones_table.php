<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devoluciones', function (Blueprint $table) {
            // solo se llenan cuando la venta original fue de tipo 'mixto' -- el cajero indica
            // cuanto de esta devolucion especifica se regresa en efectivo vs en tarjeta. Para
            // devoluciones de ventas de un solo metodo de pago se quedan en null, y
            // BoxController::store() sigue usando sale->type_payment como antes (ver nota ahi).
            $table->double('monto_efectivo')->nullable()->after('total_devolucion');
            $table->double('monto_tarjeta')->nullable()->after('monto_efectivo');
        });
    }

    public function down(): void
    {
        Schema::table('devoluciones', function (Blueprint $table) {
            $table->dropColumn(['monto_efectivo', 'monto_tarjeta']);
        });
    }
};
