<?php

namespace App\Jobs;

use App\Events\TournamentEnded;
use App\Models\Tournament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckTournamentWinnerJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Tournament $tournament)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $winner = $this->tournament->players()
            ->orderByDesc('pivot_points')
            ->orderByDesc('pivot_wins')
            ->orderBy('pivot_fouls')
            ->first();

        if ($winner) {
            $this->tournament->winner_id = $winner->id;
            $this->tournament->setAsEnded();

            #broadcast(new TournamentEnded($this->tournament));
        }
    }
}
