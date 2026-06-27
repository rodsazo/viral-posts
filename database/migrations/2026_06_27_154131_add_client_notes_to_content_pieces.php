<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notas para el cliente: texto libre que se muestra en la vista pública de la pieza,
     * para explicar al cliente cómo se realizará (no es parte del guión).
     */
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->text('client_notes')->nullable()->after('people');
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropColumn('client_notes');
        });
    }
};
