<?php
use App\Http\Controllers\Api\TournamentsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

	Route::group(['prefix' => 'tournaments'], function () {
		Route::get('/', [TournamentsController::class, 'index'])->name('api.tournaments.index');
		Route::get('/{tournament}', [TournamentsController::class, 'show'])->name('api.tournament.show');
		Route::post('/store', [TournamentsController::class, 'store'])->name('api.tournaments.store');
		Route::post('/{tournament}/simulate', [TournamentsController::class, 'simulate'])->name('api.tournaments.simulate');

		Route::group(['prefix' => '{tournament}/games'], function () {
			Route::get('/{game}', [\App\Http\Controllers\Api\GameController::class, 'show'])->name('api.tournament.games.show');
			Route::post('/{game}/simulate', [\App\Http\Controllers\Api\GameController::class, 'simulate'])->name('api.tournament.games.simulate');
		});

		Route::group(['prefix' => '{tournament}/players'], function () {
			Route::get('/{player}', [\App\Http\Controllers\Api\TournamentPlayerController::class, 'show'])->name('api.tournament.players.show');
		});
	});

	Route::group(['prefix' => 'players'], function () {
		Route::get('/', [\App\Http\Controllers\Api\PlayerController::class, 'index'])->name('api.player.index');
		Route::get('/{player}', [\App\Http\Controllers\Api\PlayerController::class, 'show'])->name('api.player.show');
	});

	/* Route::apiResource('matches', MatchController::class)->only(['show']);
	Route::get('tournaments/{tournament}/players', [PlayerController::class, 'index']);
	Route::get('tournaments/{tournament}/players/{player}', [PlayerController::class, 'show']); */
});