<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table) {
            // Factores elegidos de la evaluación RUM (Víctor Heras).
            $table->json('rum_factors')->nullable()->after('rating');
            // Producto redondeado a 1 decimal, para filtrar/ordenar.
            $table->decimal('rum', 4, 1)->nullable()->after('rum_factors');
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table) {
            $table->dropColumn(['rum_factors', 'rum']);
        });
    }
};
