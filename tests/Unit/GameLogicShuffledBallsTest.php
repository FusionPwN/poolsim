<?php

declare(strict_types=1);

use App\Services\GameLogic;

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
