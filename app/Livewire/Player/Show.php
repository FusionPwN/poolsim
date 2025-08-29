<?php

namespace App\Livewire\Player;

use App\Models\Player;
use App\Traits\MenuHistory;
use Livewire\Component;

class Show extends Component
{
	use MenuHistory;

	public Player $player;

	public function mount(Player $player): void
	{
		$this->player = $player;
		$this->addToHistory('player', get_class($this->player), $this->player->id, $this->player->name);
	}

    public function render(): \Illuminate\View\View
    {
        return view('livewire.player.show');
    }
}
