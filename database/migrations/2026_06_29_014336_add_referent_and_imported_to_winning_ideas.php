<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Las ideas ganadoras pueden venir IMPORTADAS de una idea referencial (plantilla Heras):
     * guardamos el referente viral de origen y la marca temporal de importación.
     */
    public function up(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->foreignId('viral_referent_id')->nullable()->after('ideal_follower_id')->constrained()->nullOnDelete();
            $table->timestamp('imported_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('winning_ideas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('viral_referent_id');
            $table->dropColumn('imported_at');
        });
    }
};
