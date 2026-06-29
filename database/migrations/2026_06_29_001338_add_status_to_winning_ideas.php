<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado del flujo de una idea ganadora: borrador (por defecto) → hipótesis →
     * fija | descartada. Independiente de la "validación" (que se deriva de los
     * ejemplos reales): una idea validada igual nace en borrador.
     */
    public function up(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->string('status')->default('borrador')->after('concept');
        });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
