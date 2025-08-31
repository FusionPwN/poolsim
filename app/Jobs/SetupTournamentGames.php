<?php

namespace App\Jobs;

use App\Events\GamesGenerated;
use App\Models\Tournament;
use App\Services\GameLogic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class SetupTournamentGames implements ShouldQueue
{
    use Queueable;

    public int $backoff = 10; // seconds

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
        try {
            $logic = app(GameLogic::class);
            $games = $logic->createGames($this->tournament);
            broadcast(new GamesGenerated($this->tournament, $games));

            if ($this->simulate && $games->count() > 0) {
                $batch = Bus::batch(
                    $games->map(fn($game) => new GameSimulationJob($game))->toArray()
                )->dispatch();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') { // Duplicate key error code
                // Log and continue, do not fail the job
                logger()->warning('Duplicate games detected for tournament ' . $this->tournament->id);
            } else {
                throw $e; // Rethrow other DB exceptions
            }
        }
    }
}
