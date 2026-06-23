<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plantillas de GANCHOS (hooks). Catálogo GLOBAL (como HerasTemplate).
        Schema::create('hook_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('viral_referent_id')->constrained()->cascadeOnDelete();
            $table->text('objective')->nullable();
            $table->text('notes')->nullable();
            $table->text('example_generic')->nullable();
            $table->text('example_health')->nullable();
            $table->text('example_sex')->nullable();
            $table->text('example_money')->nullable();
            $table->text('example_personal_dev')->nullable();
            $table->string('reference_url')->nullable();
            $table->json('real_examples')->nullable(); // lista de URLs de ejemplo
            $table->string('icon')->nullable();        // clase FontAwesome (p. ej. "fa-solid fa-fire")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hook_templates');
    }
};
