<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // solo se usan cuando type_payment = 'mixto' (pago dividido efectivo + tarjeta).
            // Para cualquier otro type_payment se quedan en null y todo el resto del sistema
            // sigue funcionando exactamente igual que antes (un solo metodo por venta).
            $table->double('monto_efectivo')->nullable()->after('type_payment');
            $table->double('monto_tarjeta')->nullable()->after('monto_efectivo');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['monto_efectivo', 'monto_tarjeta']);
        });
    }
};
