<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GameSimulationJob;
use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Show a game for a tournament.
     */
    public function show(Request $request, Tournament $tournament, Game $game): JsonResponse
    { 
        if ($game->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Game not found in tournament'], 404);
        }
        return response()->json($game->load(['player1', 'player2', 'winner', 'loser']));
    }

    public function simulate(Request $request, Tournament $tournament, Game $game): JsonResponse
    {
        if ($game->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Game not found in tournament'], 404);
        }

        // Dispatch the game simulation job
        $job = new GameSimulationJob($game);
        dispatch($job->onQueue('game-simulation'));

        return response()->json(['message' => 'Game simulation started'], 202);
    }
}