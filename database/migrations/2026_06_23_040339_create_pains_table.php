<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dolores / Problemas / Deseos del seguidor ideal (hermano de Preguntas/Creencias).
        Schema::create('pains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            // Un dolor SIEMPRE pertenece a un seguidor ideal (obligatorio).
            $table->foreignId('ideal_follower_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // pain | problem | desire
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pains');
    }
};
