<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table) {
            $table->string('objective')->nullable()->after('winning_idea_id'); // App\Enums\ContentObjective
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table) {
            $table->dropColumn('objective');
        });
    }
};
