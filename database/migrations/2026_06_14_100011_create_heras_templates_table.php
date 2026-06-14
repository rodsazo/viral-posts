<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo global (NO escopado por marca): las 30 plantillas de Heras
        // son compartidas por todas las cuentas.
        Schema::create('heras_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number')->unique(); // 1–30
            $table->string('name');
            $table->text('structure')->nullable();
            $table->string('suggested_format')->nullable();
            $table->string('viral_mechanism')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heras_templates');
    }
};
