<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las plantillas Heras dejan de tener "número" (no aportaba nada) y ganan una lista de
     * URLs de referencia adicionales (`reference_urls`), además de la URL principal
     * (`reference_url`, que conserva su vista previa).
     */
    public function up(): void
    {
        // SQLite no permite borrar una columna usada en un índice: soltamos el único primero.
        Schema::table('heras_templates', function (Blueprint $table): void {
            $table->dropUnique('heras_templates_number_unique');
        });

        Schema::table('heras_templates', function (Blueprint $table): void {
            $table->dropColumn('number');
            $table->json('reference_urls')->nullable()->after('reference_url');
        });
    }

    public function down(): void
    {
        Schema::table('heras_templates', function (Blueprint $table): void {
            $table->dropColumn('reference_urls');
            $table->unsignedSmallInteger('number')->nullable();
        });
    }
};
