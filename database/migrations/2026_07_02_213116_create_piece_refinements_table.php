<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hilo conversacional de refinamiento por pieza (estilo chat de Claude): el usuario
 * da instrucciones ("más cálido", "más corto") y la IA propone versiones sobre las
 * que se sigue trabajando. Cada fila es un mensaje del hilo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('piece_refinements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_piece_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role'); // user | assistant
            $table->text('body')->nullable(); // user: instrucción; assistant: nota de cambios
            $table->json('proposal')->nullable(); // assistant: {hook, story, moral, cta}
            $table->timestamps();

            $table->index(['content_piece_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piece_refinements');
    }
};
