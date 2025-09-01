<?php

declare(strict_types=1);

use App\Enums\GameStatus;
use App\Jobs\GameSimulationJob;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('returns a single game for a tournament', function () {
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $tournament->players()->attach([$player1->id, $player2->id]);
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
    ]);
    $response = $this->actingAs($user)->getJson(route('api.tournament.games.show', [$tournament, $game]));
    $response->assertOk();
    $response->assertJsonFragment(['id' => $game->id]);
});

it('returns 404 for a game not in the tournament', function () {
    $user = User::factory()->create();
    $tournament1 = Tournament::factory()->create();
    $tournament2 = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $tournament1->players()->attach([$player1->id, $player2->id]);
    
    $response = $this->actingAs($user)->getJson(route('api.tournament.games.show', [$tournament2, 3]));
    $response->assertNotFound();
});

it('starts game simulation for a tournament game', function () {
    Queue::fake();
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $tournament->players()->attach([$player1->id, $player2->id]);
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
    ]);

    $response = $this->actingAs($user)->postJson(route('api.tournament.games.simulate', [$tournament, $game]));
    $response->assertAccepted();
    $response->assertJsonFragment(['message' => 'Game simulation started']);
    Queue::assertPushed(GameSimulationJob::class, function ($job) use ($game) {
        return $job->game->id === $game->id
            && $job->game->tournament_id === $game->tournament_id;
    });
});
