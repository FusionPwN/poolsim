<?php

declare(strict_types=1);

use App\Models\Tournament;
use App\Models\Player;
use App\Http\Controllers\Api\TournamentsController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
	Event::fake();
	config(['avatar.storage_path' => class_basename(__FILE__) . '/avatars/']);
});
afterEach(function () {
	Storage::disk('local')->deleteDirectory(class_basename(__FILE__));
});

it('returns JSON when requested', function () {
	Tournament::factory()->count(2)->create();

	$trait = new TournamentsController();

	$request = Request::create('/api/tournaments', 'GET');
	$request->headers->set('Accept', 'application/json');

	$response = $trait->index($request);

	expect($response)->toBeInstanceOf(JsonResponse::class);
	expect($response->getStatusCode())->toBe(200);
	expect($response->headers->get('Content-Type'))->toContain('application/json');
	expect(json_decode($response->getContent() ?: '', true)['data'])->not->toBeEmpty();
});

it('returns tournament as JSON when requested', function () {
    $players = Player::factory()->count(2)->create();
    $tournament = Tournament::factory()->create();
    $tournament->players()->attach($players);

	$trait = new TournamentsController();

    $request = Request::create('/api/tournaments/' . $tournament->id, 'GET');
    $request->headers->set('Accept', 'application/json');
    $response = $trait->show($request, $tournament);

	expect($response)->toBeInstanceOf(JsonResponse::class);

	if ($response instanceof JsonResponse) {
		expect($response->getStatusCode())->toBe(200);
		expect($response->headers->get('Content-Type'))->toContain('application/json');
		$json = json_decode($response->getContent() ?: '', true);
		expect($json['id'])->toBe($tournament->id);
		expect($json['players'])->toHaveCount(2);
	}
});
