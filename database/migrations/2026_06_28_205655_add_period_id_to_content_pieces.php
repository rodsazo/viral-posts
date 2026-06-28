<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relaciona cada pieza con un periodo de planificación (nullable: las piezas
     * existentes quedan "sin periodo" hasta asignarlas). Si se borra el periodo,
     * la pieza queda sin periodo (no se borra).
     */
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->foreignId('period_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('period_id');
        });
    }
};
