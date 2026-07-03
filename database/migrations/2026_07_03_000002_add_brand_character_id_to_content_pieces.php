<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personaje elegido (opcional) con el que se genera/refina el guión de la pieza. Se
 * inyecta en el contexto de IA. nullOnDelete: si se borra el personaje, la pieza queda
 * sin personaje (no se pierde). Timestamp posterior a create_brand_characters (orden FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->foreignId('brand_character_id')
                ->nullable()
                ->after('winning_idea_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('brand_character_id');
        });
    }
};
