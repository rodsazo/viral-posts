<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table) {
            $table->foreignId('heras_template_id')
                ->nullable()
                ->after('account_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('heras_template_id');
        });
    }
};
