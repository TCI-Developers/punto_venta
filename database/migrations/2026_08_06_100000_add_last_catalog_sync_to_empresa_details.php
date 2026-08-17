<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_details', function (Blueprint $table) {
            // marca de tiempo de la ultima vez que se aplico (no solo se reviso) el catalogo
            // completo de la Matriz -- se manda como updated_after en la siguiente consulta,
            // para que la Matriz regrese solo lo que cambio desde entonces.
            $table->timestamp('last_catalog_sync')->nullable()->after('matriz_token');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_details', function (Blueprint $table) {
            $table->dropColumn('last_catalog_sync');
        });
    }
};
