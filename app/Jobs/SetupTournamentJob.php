<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SetupTournamentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tournament $tournament, public int $playerCount, public bool $simulate = false)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
		// Only count eligible players not in ongoing tournaments
		$eligibleCount = Player::notInOngoingTournament()->count();

		// maths to determine how many players to create
		$fromExisting = min((int) ceil($this->playerCount * 0.8), $eligibleCount);
		$toCreate = $this->playerCount - $fromExisting;

		// get random eligible players from existing ones
		$existingPlayers = Player::notInOngoingTournament()->inRandomOrder()->take($fromExisting)->get();

		// create players
		$newPlayers = collect();
		if ($toCreate > 0) {
			$newPlayers = Player::factory()->count($toCreate)->create();
		}

		$playerList = $existingPlayers->merge($newPlayers);

		// attach players to tournament with 0 points
		foreach ($playerList as $player) {
			$this->tournament->players()->attach($player->id, ['points' => 0]);
		}

		dispatch(new SetupTournamentGames($this->tournament, $this->simulate));
    }
}
