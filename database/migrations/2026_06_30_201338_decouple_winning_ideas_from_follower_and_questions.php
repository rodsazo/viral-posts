<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las ideas ganadoras pasan a ser descripciones de FORMATOS (independientes del
     * seguidor): se desligan del seguidor ideal y de las preguntas. El seguidor y su
     * contexto (preguntas/mitos/dolores) se eligen al generar la PIEZA, no en la idea.
     */
    public function up(): void
    {
        if (Schema::hasColumn('winning_ideas', 'ideal_follower_id')) {
            Schema::table('winning_ideas', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('ideal_follower_id');
            });
        }

        Schema::dropIfExists('question_winning_idea');
    }

    public function down(): void
    {
        Schema::create('question_winning_idea', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winning_idea_id')->constrained()->cascadeOnDelete();
            $table->unique(['question_id', 'winning_idea_id']);
        });

        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->foreignId('ideal_follower_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }
};
