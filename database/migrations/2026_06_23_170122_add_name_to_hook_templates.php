<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('viral_referent_id');
        });
    }

    public function down(): void
    {
        Schema::table('hook_templates', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }
};
