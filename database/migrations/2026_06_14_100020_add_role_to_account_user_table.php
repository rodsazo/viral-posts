<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_user', function (Blueprint $table) {
            // Rol del usuario dentro de la marca. Las membresías existentes pasan a admin.
            $table->string('role')->default('admin')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
