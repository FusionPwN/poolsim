<?php

namespace App\Livewire\Tournament\Player;

use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Show extends Component
{
    use \Livewire\WithPagination;

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public Tournament $tournament;
    public Player $player;

    public function mount(Tournament $tournament, Player $player): void
    {
        $this->tournament = $tournament;
        $this->player = $player;
    }

    public function refresh(): void 
    {}

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Game>
     */
    #[Computed]
    public function games(): LengthAwarePaginator
    {
        return $this->tournament->games()
            ->where(function ($query) {
                $query->where('player1_id', $this->player->id)
                      ->orWhere('player2_id', $this->player->id);
            })
            ->tap(fn($query) => $query->orderBy('created_at', 'desc')->orderBy('sequence', 'asc'))
            ->paginate(
                perPage: $this->perPage
            );
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tournament.player.show');
    }
}
