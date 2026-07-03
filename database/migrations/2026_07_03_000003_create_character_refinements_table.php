<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hilo conversacional de refinamiento de un Personaje de Marca (estilo chat): el usuario
 * pide ajustes ("cambia el enemigo", "haz el arquetipo más cercano") y la IA propone una
 * versión revisada del documento. Cada fila es un mensaje. Timestamp posterior a
 * create_brand_characters (orden FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_refinements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role'); // user | assistant
            $table->text('body')->nullable(); // user: instrucción; assistant: nota de cambios
            $table->json('proposal')->nullable(); // assistant: versión propuesta del personaje (campos)
            $table->timestamps();

            $table->index(['brand_character_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_refinements');
    }
};
