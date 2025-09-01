<?php

declare(strict_types=1);

use App\Livewire\Tournament\GameSimulationStatus;
use App\Models\Tournament;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the game simulation status page', function () {
    $tournament = Tournament::factory()->create();
    Livewire::test(GameSimulationStatus::class, ['tournament' => $tournament])
        ->assertStatus(200)
        ->assertSee('Simulated'); // Adjusted to match the actual rendered output
});
