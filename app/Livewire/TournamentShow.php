<?php

namespace App\Livewire;

use App\Models\Tournament;
use App\Traits\MenuHistory;
use Livewire\Component;

class TournamentShow extends Component
{
	use MenuHistory;

	public Tournament $tournament;

	public function mount(Tournament $tournament): void
	{
		$this->tournament = $tournament;
		$this->addToHistory('tournament', get_class($this->tournament), $this->tournament->id, $this->tournament->name);
	}

    public function render(): \Illuminate\View\View
    {
        return view('livewire.tournament-show');
    }
}
