<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tournament;
use Illuminate\Validation\ValidationException;


class TournamentForm extends Component
{
    public string $name = '';
    public int $players = 2;

	/**
	 * @return array{name: array<int, string>, players: array<int, string>}
	 */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'players' => ['required', 'integer', 'min:2'],
        ];
    }

    public function createTournament(): void
    {
        $validated = $this->validate();
        Tournament::create([
            'name' => $validated['name']
        ]);
        $this->reset(['name', 'players']);
        $this->dispatch('tournamentCreated');

		$this->modal('new-tournament')->close();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.tournament-form');
    }
}
