<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CTAs (llamadas a la acción) de la marca. Cada marca puede tener varias.
        Schema::create('content_ctas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // follow | keyword
            $table->string('text', 600);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_ctas');
    }
};
