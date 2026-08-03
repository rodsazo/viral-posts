<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elección (opcional) de conocimiento viral con el que se genera/refina el guion:
 * la GUÍA de principios rectores y el SUBFORMATO. El "formato principal" reutiliza la
 * columna `format` (enum ContentFormat). Son claves del catálogo en código (config/viral.php),
 * no FKs. Se inyectan en el prompt de IA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('viral_principles_key')->nullable()->after('format');
            $table->string('viral_subformat_key')->nullable()->after('viral_principles_key');
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropColumn(['viral_principles_key', 'viral_subformat_key']);
        });
    }
};
