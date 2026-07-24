<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_user', function (Blueprint $table) {
            // null/vacio = asignado manualmente (pantalla Usuarios o Sucursales de POSTCI).
            // 'matriz' = asignado por sincronizacion con Matriz -- solo estas filas se
            // reemplazan/revocan automaticamente en importCatalogoFromMatriz() y
            // syncUserBranchFromMatriz(), para no pisar accesos asignados a mano.
            $table->string('source')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('branch_user', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
