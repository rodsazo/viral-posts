<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table) {
            // URL del post viral de referencia (Instagram, TikTok, Facebook, etc.).
            $table->string('reference_url')->nullable()->after('viral_mechanism');
        });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table) {
            $table->dropColumn('reference_url');
        });
    }
};
