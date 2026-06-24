<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ejemplos reales (URLs de posts virales de otros creadores). Una idea con al
        // menos uno se considera "Validada". Sustituye al antiguo reference_url (único).
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->json('example_urls')->nullable()->after('reference_url');
        });

        // Funde el reference_url existente en la lista de ejemplos como primer elemento.
        DB::table('winning_ideas')
            ->whereNotNull('reference_url')
            ->where('reference_url', '!=', '')
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('winning_ideas')
                    ->where('id', $row->id)
                    ->update(['example_urls' => json_encode([$row->reference_url])]);
            });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->dropColumn('example_urls');
        });
    }
};
