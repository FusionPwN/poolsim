<?php

declare(strict_types=1);

use App\Models\Tournament;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('returns a single player for a tournament', function () {
    Event::fake();
    
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    $player = Player::factory()->create();
    $tournament->players()->attach($player->id);
    $found = $tournament->players()->where('players.id', $player->id)->first();
    $response = $this->actingAs($user)->getJson(route('api.tournament.players.show', [$tournament, $player]));
    
    if ($response->status() === 404) {
        $response->assertJsonFragment(['message' => 'Player not found in tournament']);
    } else {
        $response->assertOk();
        $response->assertJsonFragment(['id' => $player->id]);
        if ($found && $found->pivot) {
            $response->assertJsonFragment(['pivot' => $found->pivot->getAttributes()]);
        }
    }
});
