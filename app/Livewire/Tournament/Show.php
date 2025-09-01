<?php

namespace App\Livewire\Tournament;

use App\Enums\TournamentStatus;
use App\Jobs\GameSimulationJob;
use App\Models\Player;
use App\Models\Tournament;
use App\Traits\MenuHistory;
use Livewire\Component;

class Show extends Component
{
	use MenuHistory;

	public Tournament $tournament;

	public function mount(Tournament $tournament): void
	{
		$this->tournament = $tournament;
		$this->addToHistory('tournament', get_class($this->tournament), $this->tournament->id, $this->tournament->name);
	}

	public function simulateAll(): void
	{
		foreach ($this->tournament->games as $game) {
			$job = new GameSimulationJob($game);
			dispatch($job->onQueue('game-simulation'));
		}
	}

	public function refresh(): void
	{}

	public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.show');
    }
}
