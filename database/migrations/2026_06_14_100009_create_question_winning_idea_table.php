<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_winning_idea', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winning_idea_id')->constrained()->cascadeOnDelete();

            $table->unique(['question_id', 'winning_idea_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_winning_idea');
    }
};
