<?php

declare(strict_types=1);

use App\Livewire\Tournament\Scoreboard;
use App\Models\Tournament;
use App\Models\Player;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('renders the tournament scoreboard page', function () {
    $tournament = Tournament::factory()->create();
    Livewire::test(Scoreboard::class, ['tournament' => $tournament])
        ->assertStatus(200)
        ->assertSee('Scoreboard');
});

it('paginates and ranks players by points, wins, fouls', function () {
    Event::fake();
    
    $tournament = Tournament::factory()->create();
    $players = [
        Player::create(['name' => 'Alice']),
        Player::create(['name' => 'Bob']),
        Player::create(['name' => 'Charlie']),
    ];
    // Attach players with pivot data
    $tournament->players()->attach($players[0]->id, ['points' => 10, 'wins' => 2, 'fouls' => 1]);
    $tournament->players()->attach($players[1]->id, ['points' => 15, 'wins' => 1, 'fouls' => 0]);
    $tournament->players()->attach($players[2]->id, ['points' => 10, 'wins' => 3, 'fouls' => 2]);

    $component = Livewire::test(Scoreboard::class, ['tournament' => $tournament]);
    $result = $component->instance()->players();
    expect($result)->toHaveCount(3);
    // Check ranking order
    expect($result[0]->name)->toBe('Bob'); // highest points
    expect($result[1]->name)->toBe('Charlie'); // more wins than Alice
    expect($result[2]->name)->toBe('Alice');
    // Check position property
    expect($result[0]->position)->toBe(1);
    expect($result[1]->position)->toBe(2);
    expect($result[2]->position)->toBe(3);
});
