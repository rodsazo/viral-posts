<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table): void {
            $table->text('brand_promise')->nullable()->after('description');
            $table->text('main_offers')->nullable()->after('brand_promise');
            $table->text('ideal_customer_profile')->nullable()->after('main_offers');
        });

        Schema::table('ideal_followers', function (Blueprint $table): void {
            // Nivel de conciencia (Heras): 0 inconsciente … 4 consciente del producto.
            $table->unsignedTinyInteger('awareness_level')->nullable()->after('description');
        });

        Schema::table('beliefs', function (Blueprint $table): void {
            // Enlace directo opcional a un seguidor ideal (además del M2M con preguntas).
            $table->foreignId('ideal_follower_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beliefs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('ideal_follower_id');
        });

        Schema::table('ideal_followers', function (Blueprint $table): void {
            $table->dropColumn('awareness_level');
        });

        Schema::table('accounts', function (Blueprint $table): void {
            $table->dropColumn(['brand_promise', 'main_offers', 'ideal_customer_profile']);
        });
    }
};
