<?php

declare(strict_types=1);

use App\Events\PlayerCreated;
use App\Models\Player;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    config(['avatar.storage_path' => class_basename(__FILE__) . '/avatars/']);
});
afterEach(function () {
    Storage::disk('public')->deleteDirectory(class_basename(__FILE__));
});

it('triggers PlayerCreated event on player creation', function () {
    Event::fake();
    
    Player::factory()->create([
        'name' => 'Test Player',
        'avatar_original' => 'original.png',
        'avatar_processed' => 'processed.png',
    ]);

    Event::assertDispatched(PlayerCreated::class, function ($event) {
        return $event->player->name === 'Test Player';
    });
});
