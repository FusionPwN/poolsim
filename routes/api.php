<?php
use App\Http\Controllers\Api\TournamentsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

	Route::group(['prefix' => 'tournaments'], function() {
		Route::get('/', [TournamentsController::class, 'index'])->name('api.tournaments.index');
		Route::post('/store', [TournamentsController::class, 'store'])->name('api.tournaments.store');
		Route::post('/{tournament}/simulate', [TournamentsController::class, 'simulate'])->name('api.tournaments.simulate');
	});

	/* Route::apiResource('matches', MatchController::class)->only(['show']);
	Route::get('tournaments/{tournament}/players', [PlayerController::class, 'index']);
	Route::get('tournaments/{tournament}/players/{player}', [PlayerController::class, 'show']); */
});