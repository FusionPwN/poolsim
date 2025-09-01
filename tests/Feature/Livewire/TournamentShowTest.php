<?php

declare(strict_types=1);

use App\Livewire\Tournament\Show;
use App\Models\Tournament;
use App\Models\Game;
use App\Models\Player;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\actingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('renders the tournament show page', function () {
    $tournament = Tournament::factory()->create();
    Livewire::test(Show::class, ['tournament' => $tournament])
        ->assertStatus(200)
        ->assertSee($tournament->name);
});

it('adds tournament to menu history on mount', function () {
    $tournament = Tournament::factory()->create();
    $component = Livewire::test(Show::class, ['tournament' => $tournament]);
        $menuHistory = $component->instance()->getMenuHistory();
        expect($menuHistory)
            ->toBeArray()
            ->toHaveCount(1);
});

it('simulates all games in the tournament', function () {
    Event::fake();
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $games = [];
    $games[] = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => 'scheduled',
    ]);
    $games[] = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player2->id,
        'player2_id' => $player1->id,
        'status' => 'scheduled',
    ]);
    $component = Livewire::test(Show::class, ['tournament' => $tournament]);
    $component->call('simulateAll');
    foreach ($games as $game) {
        assertDatabaseHas('games', ['id' => $game->id, 'tournament_id' => $tournament->id]);
    }
});
