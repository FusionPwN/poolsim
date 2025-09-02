<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('returns a paginated list of players', function () {
    Event::fake();

    $user = User::factory()->create();
    Player::factory()->count(3)->create();
    $response = $this->actingAs($user)->getJson(route('api.player.index'));
    $response->assertOk();
    $response->assertJsonStructure(['data']);
});

it('returns a single player', function () {
    Event::fake();
    
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $response = $this->actingAs($user)->getJson(route('api.player.show', $player));
    $response->assertOk();
    $response->assertJsonFragment(['id' => $player->id]);
});
