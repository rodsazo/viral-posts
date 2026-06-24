<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Las creencias dejan de relacionarse con preguntas: cuelgan del seguidor ideal.
        Schema::dropIfExists('belief_question');
    }

    public function down(): void
    {
        Schema::create('belief_question', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('belief_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unique(['belief_id', 'question_id']);
        });
    }
};
