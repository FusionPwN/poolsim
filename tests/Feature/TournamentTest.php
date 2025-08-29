<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Models\Tournament;
use App\Jobs\SetupTournamentJob;
use App\Models\Player;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
	config(['avatar.storage_path' => class_basename(__FILE__) . '/avatars/']);
});
afterEach(function () {
	Storage::disk('local')->deleteDirectory(class_basename(__FILE__));
});

it('creates tournament and dispatches setup job', function () {
	Bus::fake();

	$name = 'Summer Pool Bash';
	$playerCount = 8;

	$tournament = Tournament::new($name, $playerCount, false);

	expect($tournament)->not->toBeNull();
	expect($tournament->name)->toBe($name);
	expect($tournament->status)->toBe(TournamentStatus::OPEN);
	expect($tournament->id)->not->toBeNull();

	assertDatabaseHas('tournaments', [
		'id' => $tournament->id,
		'name' => $name,
		'status' => TournamentStatus::OPEN,
	]);

	Bus::assertDispatched(SetupTournamentJob::class, function ($job) use ($tournament, $playerCount) {
		return $job->tournament->id === $tournament->id && $job->playerCount === $playerCount;
	});
});

it('attaches correct number of players to tournament', function () {
	Player::factory()->count(10)->create();

	$tournament = Tournament::create([
		'name' => 'Test Tournament',
		'status' => TournamentStatus::OPEN,
	]);
	$playerCount = 8;

	(new SetupTournamentJob($tournament, $playerCount))->handle();

	expect($tournament->players()->count())->toBe($playerCount);

	$tournament->players->each(function ($player) {
		expect($player->getRelation('pivot')->points)->toBe(0);
	});
});

it('creates new players if not enough exist', function () {
	Player::factory()->count(2)->create();

	$tournament = Tournament::create([
		'name' => 'Test Tournament',
		'status' => TournamentStatus::OPEN,
	]);
	$playerCount = 5;

	(new SetupTournamentJob($tournament, $playerCount))->handle();

	expect($tournament->players()->count())->toBe($playerCount);

	$tournament->players->each(function ($player) {
		expect($player->getRelation('pivot')->points)->toBe(0);
	});
});

it('does not attach duplicate players', function () {
	Player::factory()->count(5)->create();

	$tournament = Tournament::create([
		'name' => 'Test Tournament',
		'status' => TournamentStatus::OPEN,
	]);
	$playerCount = 5;

	(new SetupTournamentJob($tournament, $playerCount))->handle();

	$playerIds = $tournament->players->pluck('id');
	expect($playerIds->unique()->count())->toBe($playerCount);
});
