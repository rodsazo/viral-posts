<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las URLs de imagen de previsualización (y los posts de referencia) pueden ser muy
     * largas — sobre todo cuando son `data:` URIs en base64 — y desbordan el VARCHAR(255)
     * en MySQL (error 1406). Las pasamos a TEXT. (SQLite no validaba la longitud.)
     */
    public function up(): void
    {
        Schema::table('heras_templates', function (Blueprint $table): void {
            $table->text('reference_url')->nullable()->change();
            $table->text('preview_image_url')->nullable()->change();
        });

        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->text('preview_image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('heras_templates', function (Blueprint $table): void {
            $table->string('reference_url')->nullable()->change();
            $table->string('preview_image_url')->nullable()->change();
        });

        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('preview_image_url')->nullable()->change();
        });
    }
};
