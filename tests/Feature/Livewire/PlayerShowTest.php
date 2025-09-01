<?php

declare(strict_types=1);

use App\Livewire\Player\Show;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\Game;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the player show component with tournaments and games', function () {
    $player = Player::factory()->create();
    $tournament1 = Tournament::factory()->create();
    $tournament2 = Tournament::factory()->create();
    // Attach player to tournaments with pivot points
    $player->tournaments()->attach($tournament1->id, ['points' => 12]);
    $player->tournaments()->attach($tournament2->id, ['points' => 8]);
    $opponent = Player::factory()->create();
    $game1 = Game::create([
        'tournament_id' => $tournament1->id,
        'player1_id' => $player->id,
        'player2_id' => $opponent->id,
        'sequence' => 1,
    ]);
    $game2 = Game::create([
        'tournament_id' => $tournament2->id,
        'player1_id' => $opponent->id,
        'player2_id' => $player->id,
        'sequence' => 2,
    ]);
    Livewire::test(Show::class, ['player' => $player])
        ->assertStatus(200)
        ->assertSee($player->name)
        ->assertSee($tournament1->name)
        ->assertSee($tournament2->name)
        ->assertSee('12')
        ->assertSee('8');
});

it('paginates tournaments and games for the player', function () {
    $player = Player::factory()->create();
    $tournaments = Tournament::factory()->count(15)->create();
    foreach ($tournaments as $i => $tournament) {
        $player->tournaments()->attach($tournament->id, ['points' => 10 + $i]);
        $opponent = Player::factory()->create();
        Game::create([
            'tournament_id' => $tournament->id,
            'player1_id' => $player->id,
            'player2_id' => $opponent->id,
            'sequence' => $i + 1,
        ]);
    }
    Livewire::test(Show::class, ['player' => $player])
        ->set('perPage', 10)
        ->assertStatus(200)
        ->assertSee($player->name)
        ->assertSee('Showing')
        ->assertSee('results')
        ->assertSee('10');
})
;
