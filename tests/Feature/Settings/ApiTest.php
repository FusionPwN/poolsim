<?php

declare(strict_types=1);

use App\Livewire\Settings\Api;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders the api settings page', function () {
	$user = User::factory()->create([
		'password' => Hash::make('password'),
	]);

	$this->actingAs($user);

    Livewire::test(Api::class)
        ->assertStatus(200)
        ->assertSee('api'); // Adjust to match expected content
});

it('generates a new api token', function () {
	$user = User::factory()->create([
		'password' => Hash::make('password'),
	]);

	$this->actingAs($user);

    Livewire::test(Api::class)
        ->call('generateApiToken')
        ->assertSet('apiToken', function ($value) {
            return is_string($value) && strlen($value) > 40;
        });

    expect($user->tokens()->where('name', 'api-token')->exists())->toBeTrue();
});

it('deletes old api tokens before generating a new one', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $user->createToken('api-token');
    $user->createToken('api-token');

    Livewire::test(Api::class)
        ->call('generateApiToken');

    expect($user->tokens()->where('name', 'api-token')->count())->toBe(1);
});
