<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personaje de Marca: la "fuente de verdad" sobre quién es la marca frente a cámara.
 * Entidad independiente por marca (varios personajes por cuenta). Estructura = las 9
 * secciones del framework (handoff): escalares para los campos únicos, JSON para las
 * partes que son listas (posturas, enemigos concretos, props, CTAs, reglas…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_characters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');

            // 0 · Esencia + promesa
            $table->text('essence')->nullable();
            $table->text('promise_line')->nullable();

            // 1 · Rol arquetípico
            $table->text('archetype')->nullable();
            $table->text('archetype_why')->nullable();
            $table->text('authority_source')->nullable();

            // 2 · Enemigo común
            $table->text('enemy_abstract')->nullable();
            $table->json('enemies_concrete')->nullable(); // array<string>
            $table->text('polarization_rule')->nullable();

            // 3 · Posturas defendibles
            // array<{statement, why, kind: 'principal'|'secundaria', bridge: bool}>
            $table->json('postures')->nullable();

            // 4 · Historia de origen (3 duraciones)
            $table->text('origin_full')->nullable();
            $table->text('origin_reel')->nullable();
            $table->text('origin_oneliner')->nullable();

            // 5 · Voz y energía
            $table->text('voice_tone')->nullable();
            $table->text('voice_jargon')->nullable();
            $table->text('voice_rhythm')->nullable();
            $table->text('voice_humor')->nullable();
            $table->text('verbal_signature')->nullable();

            // 6 · Identidad visual
            $table->text('visual_principle')->nullable();
            $table->text('visual_outfit')->nullable();
            $table->text('visual_look')->nullable();
            $table->text('visual_environment')->nullable();
            $table->json('visual_props')->nullable(); // array<{description, moment}>

            // 7 · Formatos de producción naturales
            $table->json('production_formats')->nullable(); // array<string>

            // 8 · Conexión con la conversión
            $table->text('conversion_destination')->nullable();
            $table->text('conversion_chain')->nullable();
            $table->json('valid_ctas')->nullable(); // array<string>

            // 9 · Reglas de coherencia (guardrails)
            $table->json('coherence_rules')->nullable(); // array<string>

            // Insumos con los que se generó (para regenerar / dar contexto al refinamiento).
            $table->json('generation_inputs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_characters');
    }
};
