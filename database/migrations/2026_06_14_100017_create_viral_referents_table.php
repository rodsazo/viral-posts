<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo global de referentes virales (p. ej. "Víctor Heras").
        Schema::create('viral_referents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niche_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->string('instagram_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viral_referents');
    }
};
