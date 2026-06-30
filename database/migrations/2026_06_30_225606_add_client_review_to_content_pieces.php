<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Respuesta del cliente desde la vista pública: aprobar o pedir cambios (con nota).
     */
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('client_review_status')->default('pending')->after('client_notes');
            $table->text('client_review_notes')->nullable()->after('client_review_status');
            $table->timestamp('client_reviewed_at')->nullable()->after('client_review_notes');
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropColumn(['client_review_status', 'client_review_notes', 'client_reviewed_at']);
        });
    }
};
