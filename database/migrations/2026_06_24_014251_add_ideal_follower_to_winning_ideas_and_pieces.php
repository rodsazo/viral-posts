<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El Seguidor Ideal pasa a ser el centro: idea y pieza se conectan a él.
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->foreignId('ideal_follower_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->foreignId('ideal_follower_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });

        // Backfill best-effort: la idea hereda el seguidor de su primera pregunta…
        DB::table('winning_ideas')->orderBy('id')->each(function (object $idea): void {
            $questionId = DB::table('question_winning_idea')
                ->where('winning_idea_id', $idea->id)
                ->value('question_id');

            if ($questionId === null) {
                return;
            }

            $followerId = DB::table('questions')->where('id', $questionId)->value('ideal_follower_id');

            if ($followerId !== null) {
                DB::table('winning_ideas')->where('id', $idea->id)->update(['ideal_follower_id' => $followerId]);
            }
        });

        // …y la pieza hereda el de su idea.
        DB::table('content_pieces')->whereNotNull('winning_idea_id')->orderBy('id')->each(function (object $piece): void {
            $followerId = DB::table('winning_ideas')->where('id', $piece->winning_idea_id)->value('ideal_follower_id');

            if ($followerId !== null) {
                DB::table('content_pieces')->where('id', $piece->id)->update(['ideal_follower_id' => $followerId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ideal_follower_id');
        });
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ideal_follower_id');
        });
    }
};
