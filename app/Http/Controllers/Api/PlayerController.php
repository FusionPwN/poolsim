<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * List all players.
     */
    public function index(Request $request): JsonResponse
    {
        $players = Player::paginate($request->input('per_page', 25));
        return response()->json($players);
    }

    /**
     * Show a player.
     */
    public function show(Request $request, Player $player): JsonResponse
    {
        return response()->json($player);
    }
}
