<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Scoreboard extends Component
{
    use \Livewire\WithPagination;

    public string $sortBy = 'points';
	public string $sortDirection = 'desc';
	public int $perPage = 25;

    public Tournament $tournament;

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function refresh(): void
    {}

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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Player&object{pivot: \Illuminate\Database\Eloquent\Relations\Pivot}>
     */
    #[Computed]
    public function players(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->tournament->players()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(
                perPage: $this->perPage,
            );
    }

    public function render()
    {
        return view('livewire.tournament.scoreboard');
    }
}
