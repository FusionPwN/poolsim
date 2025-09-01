<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Scoreboard extends Component
{
    use \Livewire\WithPagination;

	public int $perPage = 25;

    public Tournament $tournament;

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    public function refresh(): void
    {}

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Player&object{pivot: \Illuminate\Database\Eloquent\Relations\Pivot}>
     */
    #[Computed]
    public function players(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginator = $this->tournament->players()
            ->orderByDesc('pivot_points')
            ->orderByDesc('pivot_wins')
            ->orderBy('pivot_fouls')
            ->paginate(
                perPage: $this->perPage,
            );

        // Add position index to each player
        $start = ($paginator->currentPage() - 1) * $paginator->perPage();
        foreach ($paginator as $i => $player) {
            $player->position = $start + $i + 1;
        }

        return $paginator;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.scoreboard');
    }
}
