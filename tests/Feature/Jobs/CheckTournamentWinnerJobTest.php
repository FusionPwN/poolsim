<?php

declare(strict_types=1);

use App\Enums\GameStatus;
use App\Events\TournamentEnded;
use App\Enums\TournamentStatus;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Support\Facades\Event;
use App\Jobs\GameSimulationJob;
use App\Jobs\CheckTournamentWinnerJob;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('sets winner by points, then wins, then least fouls and broadcasts TournamentEnded', function () {
    Event::fake();

    $tournament = Tournament::create([
        'name' => 'Test Tournament',
        'status' => TournamentStatus::ONGOING,
    ]);
    $players = Player::factory()->count(3)->create();
    $tournament->players()->attach($players[0]->id, ['points' => 10, 'wins' => 2, 'fouls' => 5]);
    $tournament->players()->attach($players[1]->id, ['points' => 10, 'wins' => 2, 'fouls' => 3]); // least fouls
    $tournament->players()->attach($players[2]->id, ['points' => 8, 'wins' => 3, 'fouls' => 1]);

    (new CheckTournamentWinnerJob($tournament))->handle();
    $tournament->refresh();

    expect($tournament->winner_id)->toBe($players[1]->id);
    expect($tournament->isEnded())->toBeTrue();
});

it('does nothing if there are no players', function () {
    Event::fake();

    $tournament = Tournament::create([
        'name' => 'Empty Tournament',
        'status' => TournamentStatus::ONGOING,
    ]);

    (new CheckTournamentWinnerJob($tournament))->handle();
    $tournament->refresh();

    expect($tournament->winner_id)->toBeNull();
    expect($tournament->isEnded())->toBeFalse();
});

it('dispatches CheckTournamentWinnerJob when all games are ended', function () {
    Event::fake();
    Queue::fake();

    $tournament = Tournament::create([
        'name' => 'Test Tournament',
        'status' => TournamentStatus::OPEN,
    ]);
    $players = Player::factory()->count(2)->create();
    // Attach players to tournament
    $tournament->players()->attach($players[0]->id, ['points' => 0, 'wins' => 0, 'fouls' => 0]);
    $tournament->players()->attach($players[1]->id, ['points' => 0, 'wins' => 0, 'fouls' => 0]);
    $game1 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[0]->id,
        'player2_id' => $players[1]->id,
        'status' => GameStatus::SCHEDULED,
    ]);
    $game2 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[1]->id,
        'player2_id' => $players[0]->id,
        'status' => GameStatus::SCHEDULED,
    ]);

    (new GameSimulationJob($game1, true))->handle();
    (new GameSimulationJob($game2, true))->handle();

    $game1->refresh();
    $game2->refresh();
    $tournament->refresh();

    expect($game1->status)->toBe(GameStatus::ENDED);
    expect($game2->status)->toBe(GameStatus::ENDED);

    Queue::assertPushed(CheckTournamentWinnerJob::class, function ($job) use ($tournament) {
        return $job->tournament->id === $tournament->id;
    });
});

it('does not dispatch CheckTournamentWinnerJob if not all games are ended', function () {
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
        'status' => GameStatus::ENDED,
    ]);
    $game2 = Game::create([
        'tournament_id' => $tournament->id,
        'player1_id' => $players[1]->id,
        'player2_id' => $players[0]->id,
        'status' => GameStatus::ONGOING,
    ]);

    dispatch(new GameSimulationJob($game1, true));

    Queue::assertNotPushed(CheckTournamentWinnerJob::class);
});
