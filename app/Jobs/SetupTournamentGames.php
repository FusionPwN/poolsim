<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\GameLogic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SetupTournamentGames implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tournament $tournament, public bool $simulate = false)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $logic = app(GameLogic::class);
		$games = $logic->createGames($this->tournament);

		if ($this->simulate && $games->count() > 0) {

		}
    }
}
