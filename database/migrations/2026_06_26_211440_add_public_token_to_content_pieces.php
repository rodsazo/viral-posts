<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Token público e inadivinable para compartir la vista de cliente de una pieza
     * (página pública sin login en `/p/{token}`). Se rellena para las piezas
     * existentes y, de ahí en adelante, lo genera el modelo al crear.
     */
    public function up(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('public_token', 40)->nullable()->unique()->after('id');
        });

        DB::table('content_pieces')->whereNull('public_token')->orderBy('id')
            ->pluck('id')
            ->each(function (int $id): void {
                DB::table('content_pieces')->where('id', $id)->update([
                    'public_token' => Str::random(40),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
