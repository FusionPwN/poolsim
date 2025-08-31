<?php

namespace App\Jobs;

use App\Events\PlayersGenerated;
use App\Events\TournamentUpdated;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;

class SetupTournamentJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public string $uniqueId;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tournament $tournament, public int $playerCount, public bool $simulate = false)
    {
        $this->uniqueId = 'setup-tournament-' . $tournament->id;
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
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
		$this->tournament->players()->attach($playerList);

		broadcast(new PlayersGenerated($this->tournament));

		if (app()->runningUnitTests()) {
			dispatch(new SetupTournamentGames($this->tournament, $this->simulate));
		} else {
			dispatch(new SetupTournamentGames($this->tournament, $this->simulate))
				->delay(now()->addSeconds(5));
		}
    }
}
