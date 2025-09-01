<?php

namespace App\Livewire\Game;

use App\Models\Game;
use Livewire\Component;

class Show extends Component
{
    public Game $game;
    public array $turns = [];

    public function mount(Game $game): void
    {
        $this->game = $game;
        $this->turns = $this->game->describe();
    }

    public function refresh(): void
    {}

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.game.show');
    }
}
