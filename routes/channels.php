<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();

Broadcast::channel('tournaments', function ($user) {
    return true;
});

Broadcast::channel('tournaments.{tournamentId}', function ($user, $tournamentId) {
    return true;
});

Broadcast::channel('games', function ($user) {
    return true;
});

Broadcast::channel('games.{gameId}', function ($user, $gameId) {
    return true;
});