<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Livewire\Component;

class GameSimulationStatus extends Component
{
    public Tournament $tournament;

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function refresh(): void
    {}

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.game-simulation-status');
    }
}
