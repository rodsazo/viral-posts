<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Planificación de producción / grabación. (El "listo para grabar" lo cubre el estado.)
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->string('location')->nullable()->after('rum');   // locación
            $table->text('equipment')->nullable()->after('location'); // equipo necesario
            $table->text('people')->nullable()->after('equipment');   // personas y personajes
        });
    }

    public function down(): void
    {
        Schema::table('content_pieces', function (Blueprint $table): void {
            $table->dropColumn(['location', 'equipment', 'people']);
        });
    }
};
