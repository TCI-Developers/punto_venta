<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_details', function (Blueprint $table) {
            $table->string('regimen_fiscal', 3)->nullable()->after('rfc');
            $table->string('codigo_postal', 5)->nullable()->after('regimen_fiscal');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_details', function (Blueprint $table) {
            $table->dropColumn(['regimen_fiscal', 'codigo_postal']);
        });
    }
};
