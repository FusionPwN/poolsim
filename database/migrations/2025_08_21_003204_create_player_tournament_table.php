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
		Schema::create('player_tournament', function (Blueprint $table) {
			$table->id();
			$table->foreignId('player_id')->constrained()->cascadeOnDelete();
			$table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
			$table->integer('points')->default(0);
			$table->timestamps();

			$table->unique(['player_id', 'tournament_id'], 'uniq_player_in_tournament');
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_tournament');
    }
};
