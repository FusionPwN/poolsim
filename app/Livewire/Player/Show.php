<?php

namespace App\Livewire\Player;

use App\Models\Player;
use App\Traits\MenuHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Show extends Component
{
	use MenuHistory;
	use \Livewire\WithPagination;

	public string $sortBy = 'created_at';
	public string $sortDirection = 'desc';
	public int $perPage = 10;
	public Player $player;

	public function mount(Player $player): void
	{
		$this->player = $player;
		$this->addToHistory('player', get_class($this->player), $this->player->id, $this->player->name);
	}

	public function rerfresh(): void
	{}

	/**
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Tournament>
	 * @phpstan-return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Tournament&object{pivot: \Illuminate\Database\Eloquent\Relations\Pivot}>
	 */
	#[Computed]
	public function tournaments(): LengthAwarePaginator
	{
		return $this->player->tournaments()
			->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
			->paginate(
				perPage: $this->perPage,
				pageName: 'tournaments'
			);
	}

	/**
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Game>
	 */
	#[Computed]
	public function games(): LengthAwarePaginator
	{
		return $this->player->games()
			->tap(fn($query) => $query->orderBy('created_at', 'desc')->orderBy('sequence', 'asc'))
			->paginate(
				perPage: $this->perPage,
				pageName: 'games'
			);
	}

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.player.show');
    }
}
