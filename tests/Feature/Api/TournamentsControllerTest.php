<?php

declare(strict_types=1);

use App\Enums\GameStatus;
use App\Jobs\GameSimulationJob;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('returns a paginated list of tournaments', function () {
    Event::fake();

    $user = User::factory()->create();
    Tournament::factory()->count(2)->create();
    $response = $this->actingAs($user)->getJson(route('api.tournaments.index'));
    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

it('returns a single tournament', function () {
    Event::fake();
    
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    $response = $this->actingAs($user)->getJson(route('api.tournament.show', $tournament));
    $response->assertOk();
    $response->assertJsonFragment(['id' => $tournament->id]);
});

it('creates a tournament via store', function () {
    Event::fake();
    
    $user = User::factory()->create();
    $payload = [
        'name' => 'Test Tournament',
        'players' => 3,
    ];
    $response = $this->actingAs($user)->postJson(route('api.tournaments.store'), $payload);
    $response->assertCreated();
    $response->assertJsonFragment(['name' => 'Test Tournament']);
    expect(Tournament::where('name', 'Test Tournament')->exists())->toBeTrue();
});

it('simulates a tournament via simulate', function () {
    Event::fake();
    Queue::fake();
    
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create(['name' => 'Sim Tournament']);
    $player1 = Player::factory()->create();
    $player2 = Player::factory()->create();
    $tournament->players()->attach([$player1->id, $player2->id]);
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $player1->id,
        'player2_id' => $player2->id,
        'status' => GameStatus::SCHEDULED,
    ]);
    $response = $this->actingAs($user)->postJson(route('api.tournaments.simulate', $tournament));
    $response->assertAccepted();
    $response->assertJsonFragment(['message' => 'Tournament simulation started']);
    Queue::assertPushed(GameSimulationJob::class);
});
