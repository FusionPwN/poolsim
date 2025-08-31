<?php

declare(strict_types=1);

use App\Livewire\Tournament\Form;
use App\Models\Tournament;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('creates a tournament with valid data', function () {
    Bus::fake();

    Livewire::test(Form::class)
        ->set('name', 'Test Tournament')
        ->set('players', 4)
        ->set('simulation', 'automatic')
        ->call('createTournament')
        ->assertDispatched('tournamentCreated');

    $tournament = Tournament::where('name', 'Test Tournament')->first();
    expect($tournament)->not->toBeNull();
    expect($tournament->name)->toBe('Test Tournament');
    Bus::assertDispatched(App\Jobs\SetupTournamentJob::class, function ($job) use ($tournament) {
        return $job->tournament->is($tournament) && $job->playerCount === 4;
    });
});

it('shows validation errors for invalid data', function () {
    Livewire::test(Form::class)
        ->set('name', '')
        ->set('players', 1)
        ->call('createTournament')
        ->assertHasErrors(['name', 'players']);
});
