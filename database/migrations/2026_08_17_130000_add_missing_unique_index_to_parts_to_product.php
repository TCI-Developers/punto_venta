<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    // El constraint unico (product_id, code_bar) se agrego el 2025-09-30 EDITANDO la migracion
    // original (2024_06_04_095654_create_parts_to_product_table), en vez de una migracion nueva.
    // Laravel no vuelve a correr una migracion ya registrada como ejecutada aunque su archivo
    // cambie despues -- asi que cualquier base creada/migrada ANTES de esa fecha se quedo para
    // siempre sin el indice unico, y el upsert de la carga de Excel (que depende de ese
    // constraint para el ON CONFLICT) truena con "ON CONFLICT clause does not match any
    // PRIMARY KEY or UNIQUE constraint". Esta migracion lo agrega de forma segura para esas
    // bases -- no hace nada si el indice ya existe.
    const INDEX_NAME = 'parts_to_product_product_id_code_bar_unique';

    public function up(): void
    {
        $exists = DB::selectOne(
            "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='parts_to_product' AND name = ?",
            [self::INDEX_NAME]
        );

        if ($exists) {
            return;
        }

        // por si alguna vez se colaron duplicados (product_id, code_bar) sin el constraint que
        // los evitara -- se deja solo el registro mas reciente de cada combinacion antes de
        // crear el indice, o la creacion del indice tronaria igual por datos duplicados.
        DB::statement("
            DELETE FROM parts_to_product
            WHERE id NOT IN (
                SELECT MAX(id) FROM parts_to_product GROUP BY product_id, code_bar
            )
        ");

        Schema::table('parts_to_product', function ($table) {
            $table->unique(['product_id', 'code_bar'], self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        Schema::table('parts_to_product', function ($table) {
            $table->dropUnique(self::INDEX_NAME);
        });
    }
};
