<?php

declare(strict_types=1);

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
