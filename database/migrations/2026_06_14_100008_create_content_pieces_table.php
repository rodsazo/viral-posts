<?php

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            // Decisión #5: una pieza puede existir sin idea ganadora (FK nullable).
            $table->foreignId('winning_idea_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('format')->nullable();   // App\Enums\ContentFormat
            $table->string('status')->default(ContentStatus::Planificacion->value); // App\Enums\ContentStatus
            $table->text('hook')->nullable();
            $table->text('story')->nullable();
            $table->text('moral')->nullable();
            $table->text('cta')->nullable();
            $table->string('url')->nullable();
            $table->string('rating')->nullable();    // App\Enums\ContentRating
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pieces');
    }
};
