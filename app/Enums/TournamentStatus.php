<?php

declare(strict_types=1);

namespace App\Enums;

enum TournamentStatus: string
{
	// open = created but not started; ongoing = simulating; ended = all matches ended

	case OPEN = 'open';
	case ONGOING = 'ongoing';
	case ENDED = 'ended';
}
