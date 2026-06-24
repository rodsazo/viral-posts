<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Sin huérfanas: reasignar cada creencia sin seguidor al primer seguidor de su
        //    marca (o borrarla si la marca no tiene ninguno).
        DB::table('beliefs')->whereNull('ideal_follower_id')->orderBy('id')->each(function (object $belief): void {
            $followerId = DB::table('ideal_followers')
                ->where('account_id', $belief->account_id)
                ->orderBy('id')
                ->value('id');

            if ($followerId !== null) {
                DB::table('beliefs')->where('id', $belief->id)->update(['ideal_follower_id' => $followerId]);
            } else {
                DB::table('beliefs')->where('id', $belief->id)->delete();
            }
        });

        // 2) El seguidor pasa a ser obligatorio y la creencia se borra en cascada con él.
        Schema::table('beliefs', function (Blueprint $table): void {
            $table->dropForeign(['ideal_follower_id']);
        });

        Schema::table('beliefs', function (Blueprint $table): void {
            $table->unsignedBigInteger('ideal_follower_id')->nullable(false)->change();
            $table->foreign('ideal_follower_id')->references('id')->on('ideal_followers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beliefs', function (Blueprint $table): void {
            $table->dropForeign(['ideal_follower_id']);
        });

        Schema::table('beliefs', function (Blueprint $table): void {
            $table->unsignedBigInteger('ideal_follower_id')->nullable()->change();
            $table->foreign('ideal_follower_id')->references('id')->on('ideal_followers')->nullOnDelete();
        });
    }
};
