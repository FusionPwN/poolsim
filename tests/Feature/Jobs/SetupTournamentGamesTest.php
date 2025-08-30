<?php

declare(strict_types=1);

use App\Jobs\SetupTournamentGames;
use App\Models\Tournament;
use App\Models\Player;
use App\Services\GameLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('creates games for a tournament when the job runs', function () {
    Event::fake();
    $tournament = Tournament::factory()->create();
    $players = Player::factory()->count(3)->create();
    $tournament->players()->attach($players);

    expect($tournament->games()->count())->toBe(0);

    (new SetupTournamentGames($tournament))->handle();

    // For 3 players, round-robin should create 3 games
    expect($tournament->games()->count())->toBe(3);

    $gamePlayerIds = $tournament->games()->pluck('player1_id', 'player2_id')->all();
    foreach ($gamePlayerIds as $p1 => $p2) {
        expect($players->pluck('id'))->toContain($p1);
        expect($players->pluck('id'))->toContain($p2);
        expect($p1)->not->toBe($p2);
    }
});
