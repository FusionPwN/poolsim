<?php

declare(strict_types=1);

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a paginated list of tournaments', function () {
    $user = User::factory()->create();
    Tournament::factory()->count(2)->create();
    $response = $this->actingAs($user)->getJson(route('api.tournaments.index'));
    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

it('returns a single tournament', function () {
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    $response = $this->actingAs($user)->getJson(route('api.tournament.show', $tournament));
    $response->assertOk();
    $response->assertJsonFragment(['id' => $tournament->id]);
});
