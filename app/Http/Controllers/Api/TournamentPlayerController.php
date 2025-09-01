<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TournamentPlayerController extends Controller
{
    /**
     * Show a player for a tournament.
     */
    public function show(Request $request, Tournament $tournament, Player $player): JsonResponse
    {
        $found = $tournament->players()->where('players.id', $player->id)->first();
        
        if (!$found) {
            return response()->json(['message' => 'Player not found in tournament'], 404);
        }

        $data = $found->getAttributes();

        if ($found->pivot) {
            $data['pivot'] = $found->pivot->getAttributes();
        }

        return response()->json($data);
    }
}
