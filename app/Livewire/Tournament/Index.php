<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
	#[Url]
	public string $status = '';

	#[\Livewire\Attributes\On('tournamentCreated')]
	public function tournamentCreated(): void
	{
		// This method is called when the event is received; triggers re-render
	}

	public function filterByStatus(string $status): void
	{
		$this->status = $status;
		$this->resetPage();
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
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Tournament>
	 */
	#[Computed]
	public function tournaments(): LengthAwarePaginator
	{
		return Tournament::query()
			->when($this->search, function ($query) {
				$query->where('name', 'like', "%{$this->search}%");
			})
			->when($this->status, function ($query) {
				$query->where('status', $this->status);
			})
			->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
			->paginate(
				perPage: $this->perPage,
			);
	}

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.index');
    }
}
