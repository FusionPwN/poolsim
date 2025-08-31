<?php

namespace App\Livewire;

use App\Models\Tournament;
use Livewire\Component;

class TournamentMenuItem extends Component
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
        return view('livewire.tournament-menu-item');
    }
}
