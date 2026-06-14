<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winning_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('concept');
            $table->string('viral_mechanism')->nullable();
            $table->timestamps();
            // heras_template_id se añade en P1
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winning_ideas');
    }
};
