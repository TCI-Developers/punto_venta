<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->nullable();
            $table->string('folio_fiscal', 36)->nullable();
            $table->string('serie', 10)->nullable();
            $table->string('folio', 20)->nullable();
            $table->string('tipo_comprobante', 1)->default('I'); // I=Ingreso, E=Egreso, P=Pago
            $table->unsignedBigInteger('customer_id')->nullable(); // null = público general
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('forma_pago', 3)->nullable();   // 01=Efectivo, 04=Tarjeta, etc.
            $table->string('metodo_pago', 3)->nullable();  // PUE o PPD
            $table->string('uso_cfdi', 3)->nullable();     // S01, G03, etc.
            $table->string('moneda', 3)->default('MXN');
            $table->tinyInteger('status')->default(0);     // 0=pendiente, 1=timbrada, 2=cancelada, 3=error
            $table->longText('xml')->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->longText('response_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
