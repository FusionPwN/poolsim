<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();

Broadcast::channel('tournaments.{tournamentId}', function () {
    return true;
});