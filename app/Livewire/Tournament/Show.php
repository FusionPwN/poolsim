<?php

namespace App\Livewire\Tournament;

use App\Enums\TournamentStatus;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Traits\MenuHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Show extends Component
{
	use \Livewire\WithPagination;
	use MenuHistory;

	public string $sortBy = 'points';
	public string $sortDirection = 'desc';
	public int $perPage = 25;

	public Tournament $tournament;
	public ?Player $winner = null;

	public function mount(Tournament $tournament): void
	{
		$this->tournament = $tournament;
		$this->winner = $tournament->winner();
		$this->addToHistory('tournament', get_class($this->tournament), $this->tournament->id, $this->tournament->name);
	}

	public function sort(string $column): void
	{
		if ($this->sortBy === $column) {
			$this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
		} else {
			$this->sortBy = $column;
			$this->sortDirection = 'asc';
		}
	}

	public function simulateAll(): void
	{
		$this->tournament->status = TournamentStatus::ONGOING;
		$this->tournament->save();
		
	}

	public function simulate(Game $game): void
	{
		$this->tournament->status = TournamentStatus::ONGOING;
		$this->tournament->save();

		$game->simulate();
	}

	public function refresh(): void
	{
		
	}

	/**
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Player&object{pivot: \Illuminate\Database\Eloquent\Relations\Pivot}>
	 */
	#[Computed]
	public function players(): LengthAwarePaginator
	{
		return $this->tournament->players()
			->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
			->paginate(
				perPage: $this->perPage,
			);
	}

	public function render(): \Illuminate\View\View
    {
        return view('livewire.tournament.show');
    }
}
