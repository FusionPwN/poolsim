<?php

namespace App\Jobs;

use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Models\Game;
use App\Services\GameLogic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GameSimulationJob implements ShouldQueue
{
    
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    public function __construct(public Game $game, public bool $skipSleep = false)
    {
    }

    public function handle(): void
    {
        $logic = app(GameLogic::class);
        broadcast(new GameStarted($this->game->tournament_id, $this->game->id));

        if (! $this->skipSleep) {
            sleep(5);
        }
        
        $logic->runSimulation($this->game, $this->game->players());
        broadcast(new GameFinished($this->game->tournament_id, $this->game->id));

        if ($this->game->tournament->games()->ended()->count() === $this->game->tournament->games()->count()) {
            if (app()->runningUnitTests()) {
                dispatch(new CheckTournamentWinnerJob($this->game->tournament))->onQueue('setup-tournament');
            } else {
                dispatch(new CheckTournamentWinnerJob($this->game->tournament))->onQueue('setup-tournament')
                    ->delay(now()->addSeconds(10));
            }
        }
    }
}
