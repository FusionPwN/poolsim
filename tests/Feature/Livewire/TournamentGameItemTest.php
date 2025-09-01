<?php

declare(strict_types=1);

use App\Livewire\Tournament\GameItem;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\Player;
use App\Enums\GameStatus;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the game item component', function () {
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
    ]);
    Livewire::test(GameItem::class, ['game' => $game, 'tournament' => $tournament])
        ->assertStatus(200)
        ->assertSee('Simulate'); // Adjust to match a static label in your Blade view
});

it('can call simulate on the game', function () {
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
    ]);
    $component = Livewire::test(GameItem::class, ['game' => $game, 'tournament' => $tournament]);
    $component->call('simulate');
    expect($game->status)->toBe(GameStatus::SCHEDULED);
});
