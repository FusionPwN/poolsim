<?php

namespace App\Livewire\Player;

use App\Models\Player;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class Index extends Component
{
	use \Livewire\WithPagination;

	#[Url]
	public string $sortBy = 'created_at';
	#[Url]
	public string $sortDirection = 'desc';
	#[Url]
	public int $perPage = 25;
	#[Url]
	public string $search = '';

	#[\Livewire\Attributes\On('playerCreated')]
	public function playerCreated(): void
	{
		// This method is called when the event is received; triggers re-render
	}

	public function updatedSearch(): void
	{
		$this->resetPage();
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

	/**
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Player>
	 */
	#[Computed]
	public function players(): LengthAwarePaginator
	{
		return Player::query()
			->when($this->search, function ($query) {
				$query->where('name', 'like', "%{$this->search}%");
			})
			->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
			->paginate(
				perPage: $this->perPage,
			);
	}

    public function render(): View
    {
        return view('livewire.player.index');
    }
}
