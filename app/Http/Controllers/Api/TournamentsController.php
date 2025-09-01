	<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GameSimulationJob;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentsController extends Controller
{
	/**
	 * List tournaments with players.
	 * @return JsonResponse
	 */
	public function index(Request $request): JsonResponse
	{
		$tournaments = Tournament::with('players');

		if ($request->has('status')) {
			$tournaments->where('status', $request->input('status'));
		}

		$tournaments = $tournaments->paginate($request->input('per_page', 25));
		
		return response()->json($tournaments, $tournaments->count() > 0 ? 200 : 204);
	}

	/**
	 * Show tournament details.
	 * @return JsonResponse | Tournament
	 */
	public function show(Request $request, Tournament $tournament): JsonResponse | Tournament
	{
		$tournament->load('players');

		return response()->json($tournament);
	}

	/**
	 * Create a new tournament.
	 * @return JsonResponse
	 */
	public function store(Request $request): JsonResponse
	{
		$tournament = Tournament::new($request->input('name'), $request->input('players'), false);

		return response()->json($tournament, 201);
	}

	public function simulate(Request $request, Tournament $tournament): JsonResponse
	{
		foreach ($tournament->games as $game) {
			$job = new GameSimulationJob($game);
			dispatch($job->onQueue('game-simulation'));
		}

		return response()->json(['message' => 'Tournament simulation started'], 202);
	}

	/**
	 * Get a list of games for a tournament.
	 * @return JsonResponse
	 */
	public function games(Request $request, Tournament $tournament): JsonResponse
	{
		$games = $tournament->games()->with(['player1', 'player2', 'winner', 'loser'])->get();
		return response()->json($games);
	}

	/**
	 * Get a list of players for a tournament.
	 * @return JsonResponse
	 */
	public function players(Request $request, Tournament $tournament): JsonResponse
	{
		$players = $tournament->players()->get();
		
		return response()->json($players);
	}
}
