<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Periodo: ventana de planificación de piezas por marca (p. ej. "Julio 2026").
     * Su estado (borrador/publicado) decide, junto con el estado de la pieza, si esta
     * es accesible por la URL pública.
     */
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('borrador');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
