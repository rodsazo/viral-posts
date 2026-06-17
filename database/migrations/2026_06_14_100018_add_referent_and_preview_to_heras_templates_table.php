<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heras_templates', function (Blueprint $table) {
            $table->foreignId('viral_referent_id')->nullable()->after('number')->constrained()->nullOnDelete();
            $table->string('reference_url')->nullable()->after('viral_mechanism'); // URL del post viral
            $table->string('preview_image_url')->nullable()->after('reference_url'); // miniatura del post
        });
    }

    public function down(): void
    {
        Schema::table('heras_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('viral_referent_id');
            $table->dropColumn(['reference_url', 'preview_image_url']);
        });
    }
};
