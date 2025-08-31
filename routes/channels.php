<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();

Broadcast::channel('tournaments.{tournamentId}', function ($user, $tournamentId) {
    return true;
});

Broadcast::channel('games.{gameId}', function ($user, $gameId) {
    return true;
});