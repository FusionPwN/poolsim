<?php

declare(strict_types=1);

namespace App\Enums;

enum GameStatus: string
{
	// scheduled = waiting for turn; ongoing = simulating; ended = game ended

	case SCHEDULED = 'scheduled';
	case ONGOING = 'ongoing';
	case ENDED = 'ended';
}
