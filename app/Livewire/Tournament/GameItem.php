<?php

namespace App\Livewire\Tournament;

use App\Models\Game;
use Livewire\Component;

class GameItem extends Component
{
    public Game $game;

    public function mount(Game $game): void
    {
        $this->game = $game;
    }

    public function refresh(): void
    {}

    public function simulate(): void
    {
        $this->game->simulate();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.game-item');
    }
}
