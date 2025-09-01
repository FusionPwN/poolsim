<?php

namespace App\Livewire\Tournament;

use App\Enums\TournamentStatus;
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
		
	}

	public function refresh(): void
	{}

	public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.show');
    }
}
