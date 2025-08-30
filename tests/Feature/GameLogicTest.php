<?php

declare(strict_types=1);

use App\Enums\TournamentStatus;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\GameLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
	config(['avatar.storage_path' => class_basename(__FILE__) . '/avatars/']);
});
afterEach(function () {
	Storage::disk('local')->deleteDirectory(class_basename(__FILE__));
});

it('initializes balls correctly', function () {
    $logic = new GameLogic();
    $logic->resetBalls();

    expect($logic->balls['cue'])->toBeTrue();
    expect($logic->balls['black'])->toBeTrue();
    expect($logic->balls['solids'])->toHaveCount(7);
    expect($logic->balls['stripes'])->toHaveCount(7);
});

it('simulates players with correct keys', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';

    expect($logic->getPlayerData())->toHaveCount(2);
    foreach ($logic->getPlayerData() as $player) {
        expect($player['id'])->not->toBeNull();
        expect($player['name'])->not->toBeNull();
        expect($player['skill'])->not->toBeNull();
    }
});

it('returns correct balls for each type', function () {
    $logic = new GameLogic();
    $logic->resetBalls();

    expect($logic->getBalls('solids'))->toBe([1,2,3,4,5,6,7]);
    expect($logic->getBalls('stripes'))->toBe([9,10,11,12,13,14,15]);
    expect($logic->getBalls('black'))->toBe([8]);
    expect($logic->getBalls('cue'))->toBe(['cue']);
    expect($logic->getBalls())->toContain(1,9,8,'cue');
});

it('correctly identifies ball types', function () {
    $logic = new GameLogic();
    $logic->resetBalls();

    expect($logic->isBallSolids(1))->toBeTrue();
    expect($logic->isBallStripes(10))->toBeTrue();
    expect($logic->getBallTypeFromNumber(8))->toBe('black');
    expect($logic->getBallTypeFromNumber(1))->toBe('solids');
    expect($logic->getBallTypeFromNumber(10))->toBe('stripes');
});

it('correctly identifies own and opponent balls', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
    $logic->current = 0;
    $logic->opponent = 1;

    expect($logic->isOwnBall(1))->toBeTrue();
    expect($logic->isOwnBall(10))->toBeFalse();
    expect($logic->isOpponentsBall(10))->toBeTrue();
    expect($logic->isOpponentsBall(1))->toBeFalse();
    expect($logic->pottedBallsBelongsToOpponent([10,1]))->toBeTrue();
    expect($logic->pottedBallsBelongsToOpponent([1]))->toBeFalse();
});

it('prioritizes balls in getShuffledBalls', function () {
    $logic = new GameLogic();
    $logic->resetBalls();

    $balls = $logic->getShuffledBalls('solids', 2);
    expect($logic->isBallSolids($balls[0]))->toBeTrue();
    expect($logic->isBallSolids($balls[1]))->toBeTrue();

    $balls = $logic->getShuffledBalls('stripes', 2);
    expect($logic->isBallStripes($balls[0]))->toBeTrue();
    expect($logic->isBallStripes($balls[1]))->toBeTrue();

    $balls = $logic->getShuffledBalls('black', 1);
    expect($balls[0])->toBe(8);
});

it('runs simulation and sets winner/loser', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$tournament = Tournament::create(['name' => 'Test Tournament', 'status' => TournamentStatus::OPEN]);
	$game = Game::create([
		'player1_id' => $players[0]->id,
		'player2_id' => $players[1]->id,
		'tournament_id' => $tournament->id,
		'status' => 'scheduled',
	]);
    $logic->resetBalls();
    $results = $logic->runSimulation($game, $players);

    expect($results['actions'])->not->toBeEmpty();
    expect($logic->gameEnded)->toBeTrue();
    expect($logic->winner)->not->toBeNull();
    expect($logic->loser)->not->toBeNull();
    expect($results)->toHaveKeys(['total_balls_left', 'balls_left_by_type', 'total_actions', 'total_fouls', 'fouls_by_player', 'actions']);
    expect($results['total_balls_left'])->toBe($results['balls_left_by_type']['solids'] + $results['balls_left_by_type']['stripes']);
});

it('fills game fields after simulation', function () {
    $logic = new GameLogic();
    $players = Player::factory()->count(2)->create();
    $tournament = Tournament::create(['name' => 'Test Tournament', 'status' => TournamentStatus::OPEN]);
    $game = Game::create([
        'player1_id' => $players[0]->id,
        'player2_id' => $players[1]->id,
        'tournament_id' => $tournament->id,
        'status' => 'scheduled',
    ]);
    $logic->resetBalls();
    $results = $logic->runSimulation($game, $players);

    expect($game->balls_left_solids)->toBe($results['balls_left_by_type']['solids']);
    expect($game->balls_left_stripes)->toBe($results['balls_left_by_type']['stripes']);
    expect($game->total_actions)->toBe($results['total_actions']);
    expect($game->total_fouls)->toBe($results['total_fouls']);
    expect($game->fouls_player1 + $game->fouls_player2)->toBe($results['total_fouls']);

    // Assert points updated on pivot table
    $winnerId = $logic->winner;
    $loserId = $logic->loser;
    $tournament->refresh();
    $winnerPoints = $tournament->players()->find($winnerId)->pivot->points;
    $loserPoints = $tournament->players()->find($loserId)->pivot->points;
    expect($winnerPoints)->toBeGreaterThanOrEqual($logic->points['win']);
    expect($loserPoints)->toBeGreaterThanOrEqual($logic->points['loss']);
});

it('handles 8-ball pot on break', function () {
	$logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
	$logic->resetBalls();
	$logic->current = 0;
	$logic->opponent = 1;

	$action = [
		'pots' => [8],
		'foul' => false,
		'foul_reason' => null,
	];
	$logic->simulateFoul($action, ...$logic->getPlayerData());

	expect($action['foul'])->toBeFalse();
	expect($action['foul_reason'])->toBe('potted_8_ball_on_break');
	expect($logic->isBreak)->toBeTrue();
	expect($logic->gameEnded)->toBeFalse();
});

it('handles illegal 8-ball pot', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
	$logic->isBreak = false;
    $logic->current = 0;
    $logic->opponent = 1;

    $action = [
        'pots' => [8],
        'foul' => false,
        'foul_reason' => null,
    ];
    $logic->simulateFoul($action, ...$logic->getPlayerData());

    expect($action['foul'])->toBeTrue();
    expect($action['foul_reason'])->toBe('potted_8_ball_illegally');
    expect($logic->gameEnded)->toBeTrue();
    expect($logic->winner)->toBe($logic->getPlayerData()[1]['id']);
    expect($logic->loser)->toBe($logic->getPlayerData()[0]['id']);
});

it('handles cue ball and black potted together', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
    $logic->current = 0;
    $logic->opponent = 1;
    $logic->isBreak = false;
    $logic->balls[$logic->playerData[$logic->current]['group']] = [];

    // Force cue ball foul to always happen
	$logic->chances['failed_to_hit_own_balls'] = 0;
	$logic->chances['cue_ball_off_table'] = 0;
	$logic->chances['black_off_table'] = 0;
	$logic->chances['potted_cue_ball'] = 100;

    $action = [
        'pots' => [8],
        'foul' => false,
        'foul_reason' => null,
    ];
    $logic->simulateFoul($action, ...$logic->getPlayerData());

    expect($action['foul_reason'])->toBe('potted_cue_ball_and_black');
    expect($logic->gameEnded)->toBeTrue();
    expect($logic->winner)->toBe($logic->getPlayerData()[1]['id']);
    expect($logic->loser)->toBe($logic->getPlayerData()[0]['id']);
});

it('handles black off table foul', function () {
    $logic = new GameLogic();
    $players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
    $logic->current = 0;
    $logic->opponent = 1;

    // Force black_off_table foul to always happen
	$logic->chances['failed_to_hit_own_balls'] = 0;
	$logic->chances['cue_ball_off_table'] = 0;
	$logic->chances['black_off_table'] = 100;
	$logic->chances['potted_cue_ball'] = 0;

    $action = [
        'pots' => [],
        'foul' => false,
        'foul_reason' => null,
    ];
    $logic->simulateFoul($action, ...$logic->getPlayerData());

    expect($action['foul_reason'])->toBe('black_off_table');
    expect($logic->gameEnded)->toBeTrue();
    expect($logic->winner)->toBe($logic->getPlayerData()[1]['id']);
    expect($logic->loser)->toBe($logic->getPlayerData()[0]['id']);
});

it('handles failed_to_hit_own_balls foul', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
    $logic->current = 0;
    $logic->opponent = 1;

    // Force failed_to_hit_own_balls foul to always happen
    $logic->chances['failed_to_hit_own_balls'] = 100;
	$logic->chances['cue_ball_off_table'] = 0;
	$logic->chances['black_off_table'] = 0;
	$logic->chances['potted_cue_ball'] = 0;

    $action = [
        'pots' => [],
        'foul' => false,
        'foul_reason' => null,
    ];
    $logic->simulateFoul($action, ...$logic->getPlayerData());

    expect($action['foul_reason'])->toBe('failed_to_hit_own_balls');
    expect($action['foul'])->toBeTrue();
});

it('handles cue_ball_off_table foul', function () {
    $logic = new GameLogic();
	$players = Player::factory()->count(2)->create();
	$logic->setPlayerData($players);
	$logic->playerData[0]['group'] = 'solids';
	$logic->playerData[1]['group'] = 'stripes';
    $logic->resetBalls();
    $logic->current = 0;
    $logic->opponent = 1;

    // Force cue_ball_off_table foul to always happen
    $logic->chances['cue_ball_off_table'] = 100;
	$logic->chances['black_off_table'] = 0;
	$logic->chances['failed_to_hit_own_balls'] = 0;
	$logic->chances['potted_cue_ball'] = 0;

    $action = [
        'pots' => [],
        'foul' => false,
        'foul_reason' => null,
    ];
    $logic->simulateFoul($action, ...$logic->getPlayerData());

    expect($action['foul_reason'])->toBe('cue_ball_off_table');
    expect($action['foul'])->toBeTrue();
});

it('returns correct chance values from getChance', function () {
	$logic = new GameLogic();
	$logic->chances['black_off_table'] = 42;
	$logic->chances['failed_to_hit_own_balls'] = 55;
	$logic->chances['potted_cue_ball'] = 66;
	$logic->chances['cue_ball_off_table'] = 77;

	expect($logic->getChance('black_off_table'))->toBe(42);
    expect($logic->getChance('failed_to_hit_own_balls'))->toBe(55);
    expect($logic->getChance('potted_cue_ball'))->toBe(66);
    expect($logic->getChance('cue_ball_off_table'))->toBe(77);
});

it('creates round-robin games for a tournament', function () {
	$tournament = Tournament::factory()->create();
	$players = Player::factory()->count(4)->create();
	$tournament->players()->attach($players);

	$logic = new GameLogic();
	$games = $logic->createGames($tournament);

	// For 4 players, round-robin should create 6 games
	expect($games)->toHaveCount(6);

	// Each game should have correct tournament_id and valid player ids
	foreach ($games as $game) {
		expect($game->tournament_id)->toBe($tournament->id);
		expect($players->pluck('id'))->toContain($game->player1_id);
		expect($players->pluck('id'))->toContain($game->player2_id);
		expect($game->player1_id)->not->toBe($game->player2_id);
	}
});

it('prioritizes the first ball according to the priority argument and chance', function () {
	$logic = new GameLogic();
	$logic->resetBalls();

	// Test 1000x if the solids balls are prioritized
	for ($i = 0; $i < 1000; $i++) {
		$balls = $logic->getShuffledBalls('solids', 2);
		expect($logic->isBallSolids($balls[0]))->toBe(true);
		expect($logic->isBallSolids($balls[1]))->toBe(true);
	}

	// test 1000x if the stripes balls are prioritized
	for ($i = 0; $i < 1000; $i++) {
		$balls = $logic->getShuffledBalls('stripes', 2);
		expect($logic->isBallStripes($balls[0]))->toBe(true);
		expect($logic->isBallStripes($balls[1]))->toBe(true);
	}
});