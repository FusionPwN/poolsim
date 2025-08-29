<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
		$tournaments = Tournament::with('players')->paginate($request->input('per_page', 25));

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
		$tournament = Tournament::new($request->input('name'), $request->input('players'), $request->input('simulate', true));

		return response()->json($tournament, 201);
	}
}
