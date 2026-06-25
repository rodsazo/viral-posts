<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganchos por marca: account_id null = gancho GLOBAL de referencia (admin/super
        // admin); con marca = gancho propio de la marca (editable en el Estudio).
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->foreignId('account_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // El referente viral pasa a ser OPCIONAL (un gancho de marca puede no venir de uno).
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->dropForeign(['viral_referent_id']);
        });
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->unsignedBigInteger('viral_referent_id')->nullable()->change();
            $table->foreign('viral_referent_id')->references('id')->on('viral_referents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('account_id');
        });
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->dropForeign(['viral_referent_id']);
        });
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->unsignedBigInteger('viral_referent_id')->nullable(false)->change();
            $table->foreign('viral_referent_id')->references('id')->on('viral_referents')->cascadeOnDelete();
        });
    }
};
