<?php

declare(strict_types=1);

use App\Livewire\Player\Index;
use App\Models\Player;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('renders the player index component with players', function () {
    Event::fake();
    
    $players = Player::factory()->count(3)->create();
    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertSee($players[0]->name)
        ->assertSee($players[1]->name)
        ->assertSee($players[2]->name);
});

it('searches for players by name', function () {
    Event::fake();

    $players = Player::factory()->count(3)->create();
    $searchName = $players[1]->name;
    Livewire::test(Index::class)
        ->set('search', $searchName)
        ->assertStatus(200)
        ->assertSee($searchName)
        ->assertDontSee($players[0]->name)
        ->assertDontSee($players[2]->name);
});

it('sorts players by name', function () {
    Event::fake();

    $players = Player::factory()->count(3)->create();
    $sorted = $players->sortBy('name')->values();
    $component = Livewire::test(Index::class)
        ->call('sort', 'name')
        ->assertStatus(200);
    // Assert the first player in sorted order is visible
    $component->assertSee($sorted[0]->name);
});

it('paginates players', function () {
    Event::fake();
    
    $players = Player::factory()->count(30)->create();
    Livewire::test(Index::class)
        ->set('perPage', 10)
        ->assertStatus(200)
        ->assertSee($players[0]->name)
        ->assertSee('Showing')
        ->assertSee('results');
});
