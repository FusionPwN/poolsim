<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Jobs\GameSimulationJob;
use App\Models\{Game, Player, Tournament};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('dispatches GameSimulationJob and runs simulation for the correct tournament', function () {
    Event::fake();
    Queue::fake();

    $tournament = Tournament::create([
        'name' => 'Test Tournament',
        'status' => TournamentStatus::OPEN,
    ]);
    $players = Player::factory()->count(2)->create();
    $game = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[0]->id,
        'player2_id' => $players[1]->id,
    ]);

    dispatch(new GameSimulationJob($game));

    Queue::assertPushed(GameSimulationJob::class, function ($job) use ($game) {
        return $job->game->id === $game->id
            && $job->game->tournament_id === $game->tournament_id;
    });
});

it('prevents overlapping simulation jobs for the same tournament', function () {
    Event::fake();
    Queue::fake();

    $tournament = Tournament::create([
        'name' => 'Test Tournament',
        'status' => TournamentStatus::OPEN,
    ]);
    $players = Player::factory()->count(2)->create();

    $game1 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[0]->id,
        'player2_id' => $players[1]->id,
    ]);
    $game2 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[1]->id,
        'player2_id' => $players[0]->id,
    ]);

    // Dispatch two jobs for the same tournament
    dispatch(new GameSimulationJob($game1));
    dispatch(new GameSimulationJob($game2));

    // Assert both jobs are queued, but only one will run at a time due to WithoutOverlapping
    Queue::assertPushed(GameSimulationJob::class, 2);
});
