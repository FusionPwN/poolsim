<?php

use App\Enums\GameStatus;
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
        Schema::create('games', function (Blueprint $table) {
			$table->id();

			$table->foreignId('tournament_id')
				->constrained()
				->cascadeOnDelete();

			$table->foreignId('player1_id')->constrained('players')->cascadeOnDelete();
			$table->foreignId('player2_id')->constrained('players')->cascadeOnDelete();

			// nullable until a match ends
			$table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
			$table->foreignId('loser_id')->nullable()->constrained('players')->nullOnDelete();

			$table->enum('status', array_map(fn($case) => $case->value, GameStatus::cases()))->default('scheduled')->index();

			$table->unsignedInteger('sequence')->nullable()->index();
			$table->timestamp('started_at')->nullable();
			$table->timestamp('ended_at')->nullable();

			$table->json('actions')->nullable();
			$table->unsignedInteger('balls_left_solids')->nullable();
			$table->unsignedInteger('balls_left_stripes')->nullable();
			$table->unsignedInteger('total_actions')->nullable();
			$table->unsignedInteger('total_fouls')->nullable();
			$table->unsignedInteger('fouls_player1')->nullable();
			$table->unsignedInteger('fouls_player2')->nullable();

			$table->timestamps();

			// A given pair can occur only once within the same tournament
			$table->unique(['tournament_id', 'player1_id', 'player2_id'], 'uniq_match_pair_per_tournament');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
