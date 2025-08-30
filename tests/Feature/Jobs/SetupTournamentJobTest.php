<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Models\{Player, Tournament};
use App\Jobs\SetupTournamentJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('attaches only eligible players to tournament', function () {
    Event::fake();
    
    // Create 5 players, 2 in ongoing tournaments, 3 not
    $ongoingTournament = Tournament::factory()->create(['status' => TournamentStatus::ONGOING]);
    $otherTournament = Tournament::factory()->create(['status' => TournamentStatus::ENDED]);

    $eligiblePlayers = Player::factory()->count(3)->create();
    $busyPlayers = Player::factory()->count(2)->create();

    // Attach busy players to ongoing tournament
    foreach ($busyPlayers as $player) {
        $ongoingTournament->players()->attach($player->id, ['points' => 0]);
    }
    // Attach eligible players to finished tournament
    foreach ($eligiblePlayers as $player) {
        $otherTournament->players()->attach($player->id, ['points' => 0]);
    }

    // Create a new tournament
    $newTournament = Tournament::factory()->create(['status' => TournamentStatus::ONGOING]);

    // Run the job to attach 4 players
    (new SetupTournamentJob($newTournament, 4))->handle();

    // Only eligible players should be attached
    $attachedIds = $newTournament->players()->pluck('players.id')->toArray();
    $eligibleIds = $eligiblePlayers->pluck('id')->toArray();

    // Get all player IDs created in this test
    $allCreatedIds = Player::pluck('id')->toArray();

    foreach ($attachedIds as $id) {
        expect($allCreatedIds)->toContain($id);
    }
    expect(count($attachedIds))->toBe(4);
});
