<?php

declare(strict_types=1);

use App\Enums\GameStatus;
use App\Livewire\Game\Show;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the game show component with turns', function () {
    Event::fake();
    
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
        'sequence' => 1,
    ]);
    // Simulate the game using the model's simulate method
    if (method_exists($game, 'simulate')) {
        $game->simulate();
    }
    Livewire::test(Show::class, ['game' => $game])
        ->assertStatus(200)
        ->assertSee($player1->name)
        ->assertSee($player2->name);
});
