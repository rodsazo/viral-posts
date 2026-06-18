<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Usuario desactivado: no puede acceder al panel ni al Estudio.
            $table->boolean('is_active')->default(true)->after('is_super_admin');
        });

        Schema::table('accounts', function (Blueprint $table): void {
            // Marca suspendida: sus miembros no pueden acceder a ella.
            $table->boolean('is_active')->default(true)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
