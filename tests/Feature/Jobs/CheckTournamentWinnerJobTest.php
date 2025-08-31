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
    Event::fake([TournamentEnded::class]);

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

    Event::assertDispatched(TournamentEnded::class, function ($event) use ($tournament) {
        return $event->tournament->id === $tournament->id;
    });
});

it('does nothing if there are no players', function () {
    Event::fake([TournamentEnded::class]);

    $tournament = Tournament::create([
        'name' => 'Empty Tournament',
        'status' => TournamentStatus::ONGOING,
    ]);

    (new CheckTournamentWinnerJob($tournament))->handle();
    $tournament->refresh();

    expect($tournament->winner_id)->toBeNull();
    expect($tournament->isEnded())->toBeFalse();
    Event::assertNotDispatched(TournamentEnded::class);
});

it('dispatches CheckTournamentWinnerJob when all games are ended', function () {
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
        'status' => GameStatus::ENDED,
    ]);

    dispatch(new GameSimulationJob($game1));

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

    dispatch(new GameSimulationJob($game1));

    Queue::assertNotPushed(CheckTournamentWinnerJob::class);
});
