<?php

declare(strict_types=1);

use App\Livewire\Tournament\Show;
use App\Models\Tournament;
use App\Models\Game;
use Livewire\Livewire;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\actingAs;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    expect($component->instance()->getMenuHistory())
        ->toBeArray()
        ->not->toBeEmpty();
});

it('simulates all games in the tournament', function () {
    $tournament = Tournament::factory()->create();
    $games = [];
    $games[] = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => 1,
        'player2_id' => 2,
        'status' => 'scheduled',
    ]);
    $games[] = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => 2,
        'player2_id' => 1,
        'status' => 'scheduled',
    ]);
    $component = Livewire::test(Show::class, ['tournament' => $tournament]);
    $component->call('simulateAll');
    foreach ($games as $game) {
        assertDatabaseHas('games', ['id' => $game->id, 'tournament_id' => $tournament->id]);
    }
});
