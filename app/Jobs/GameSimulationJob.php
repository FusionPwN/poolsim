<?php

namespace App\Jobs;

use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Models\Game;
use App\Services\GameLogic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class GameSimulationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public Game $game, public bool $skipSleep = false)
    {
    }

    public function handle(): void
    {
        $logic = app(GameLogic::class);
        broadcast(new GameStarted($this->game));

        if (! $this->skipSleep) {
            sleep(5);
        }
        
        $logic->runSimulation($this->game, $this->game->players());
        broadcast(new GameFinished($this->game));
    }

    /**
     * @return array<int, \Illuminate\Queue\Middleware\WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('tournament-simulation-' . $this->game->tournament_id))
                ->releaseAfter(10),
        ];
    }
}
