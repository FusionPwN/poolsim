<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Livewire\Tournament\Index;
use App\Models\Tournament;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders tournaments', function () {
    Tournament::factory()->count(3)->create();

    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertSee(Tournament::first()->name);
});

it('sorts tournaments by column', function () {
	Tournament::factory()->count(3)->create();

    Livewire::test(Index::class)
        ->call('sort', 'name')
        ->assertSet('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');

    Livewire::test(Index::class)
        ->set('sortBy', 'name')
        ->set('sortDirection', 'desc')
        ->call('sort', 'name')
        ->assertSet('sortDirection', 'asc');
});

it('filters tournaments by search', function () {
    $t1 = Tournament::factory()->create(['name' => 'Alpha']);
    $t2 = Tournament::factory()->create(['name' => 'Beta']);

    Livewire::test(Index::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha')
        ->assertDontSee('Beta');
});

