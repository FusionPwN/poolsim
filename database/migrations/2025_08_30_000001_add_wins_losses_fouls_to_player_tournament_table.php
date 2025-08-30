<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('player_tournament', function (Blueprint $table) {
            $table->integer('wins')->default(0)->after('points');
            $table->integer('losses')->default(0)->after('wins');
            $table->integer('fouls')->default(0)->after('losses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_tournament', function (Blueprint $table) {
            $table->dropColumn(['wins', 'losses', 'fouls']);
        });
    }
};
