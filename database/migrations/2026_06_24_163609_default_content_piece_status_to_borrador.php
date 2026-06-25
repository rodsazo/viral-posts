<?php

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Borrador" es el nuevo primer estado y el default de toda pieza nueva.
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('status')->default(ContentStatus::Borrador->value)->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('status')->default(ContentStatus::Planificacion->value)->change();
        });
    }
};
