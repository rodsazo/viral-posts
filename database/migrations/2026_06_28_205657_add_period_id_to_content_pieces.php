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
        // Idempotente: en entornos donde ya se aplicó (dev, antes del renombrado),
        // no volver a añadir la columna. Debe correr DESPUÉS de create_periods_table
        // (por eso el timestamp es posterior): MySQL valida la tabla referenciada.
        if (Schema::hasColumn('content_pieces', 'period_id')) {
            return;
        }

        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->foreignId('period_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('content_pieces', 'period_id')) {
            return;
        }

        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('period_id');
        });
    }
};
