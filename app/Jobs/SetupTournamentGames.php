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
            #broadcast(new GamesGenerated($this->tournament));

            if (!$this->tournament->isOngoing()) {
                $this->tournament->setAsOngoing();
            }

            if ($this->simulate && $games->count() > 0) {
                foreach ($games as $game) {
                    $job = new GameSimulationJob($game);
                    /* if (!app()->runningUnitTests()) {
                        $job->delay = now()->addSeconds(10);
                    } */
                    dispatch($job->onQueue('game-simulation'));
                }
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
