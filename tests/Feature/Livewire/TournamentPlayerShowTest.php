<?php

declare(strict_types=1);

use App\Livewire\Tournament\Player\Show;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\Game;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('renders the player show component with games', function () {
    Event::fake();
    
    $tournament = Tournament::factory()->create();
    $player = Player::factory()->create();
    $opponent = Player::factory()->create();
    // Attach both players to the tournament with pivot points
    $tournament->players()->attach($player->id, ['points' => 10]);
    $tournament->players()->attach($opponent->id, ['points' => 5]);
    $game1 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player->id,
        'player2_id' => $opponent->id,
        'sequence' => 1,
    ]);
    $game2 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $opponent->id,
        'player2_id' => $player->id,
        'sequence' => 2,
    ]);

    Livewire::test(Show::class, ['tournament' => $tournament, 'player' => $player])
        ->assertStatus(200)
        ->assertSee($player->name)
        ->assertSee($opponent->name)
        ->assertSee('10') // Player points
        ->assertSee('5'); // Opponent points
});

it('paginates games for the player', function () {
    Event::fake();
    
    $tournament = Tournament::factory()->create();
    $player = Player::factory()->create();
    $tournament->players()->attach($player->id, ['points' => 20]);
    $opponents = Player::factory()->count(15)->create();
    foreach ($opponents as $i => $opponent) {
        $tournament->players()->attach($opponent->id, ['points' => 15 + $i]);
        Game::create([
            'tournament_id' => $tournament->id,
            'player1_id' => $player->id,
            'player2_id' => $opponent->id,
            'sequence' => $i + 1,
        ]);
    }
    Livewire::test(Show::class, ['tournament' => $tournament, 'player' => $player])
        ->set('perPage', 10)
        ->assertStatus(200)
        ->assertSee($player->name)
        ->assertSee('Showing') // Static pagination label
        ->assertSee('results') // Static pagination label
        ->assertSee('20')
        ->assertSee('15');
});
