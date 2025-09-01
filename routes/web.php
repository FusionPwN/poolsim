<?php

use App\Livewire\Settings\Api;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Tournament\Show as TournamentShow;
use App\Livewire\Tournament\Index as TournamentIndex;
use App\Livewire\Player\Index as PlayerIndex;
use App\Livewire\Player\Show as PlayerShow;
use App\Livewire\Tournament\Player\Show as TournamentPlayerShow;
use App\Livewire\Game\Show as GameShow;
use Illuminate\Support\Facades\Route;

Route::view('dashboard', 'dashboard')
	->middleware(['auth', 'verified'])
	->name('dashboard');

Route::middleware(['auth'])->group(function () {
	Route::redirect('settings', 'settings/profile');

	Route::get('settings/profile', Profile::class)->name('settings.profile');
	Route::get('settings/password', Password::class)->name('settings.password');
	Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
	Route::get('settings/api', Api::class)->name('settings.api');

    Route::group(['prefix' => 'tournaments'], function () {
        Route::get('/', TournamentIndex::class)->name('tournaments.index');
		Route::get('/{tournament}', TournamentShow::class)->name('tournament.show');

		Route::group(['prefix' => '{tournament}/games'], function () {
			Route::get('/{game}', GameShow::class)->name('tournament.games.show');
		});

		Route::group(['prefix' => '{tournament}/players'], function () {
			Route::get('/{player}', TournamentPlayerShow::class)->name('tournament.players.show');
		});
    });

	Route::group(['prefix' => 'players'], function () {
		Route::get('/', PlayerIndex::class)->name('player.index');
		Route::get('/{player}', PlayerShow::class)->name('player.show');
	});
});

/* 
Route::get('/matches/{match}', MatchShow::class)->name('match.show');
Route::get('/players/{player}', PlayerShow::class)->name('player.show'); */

require __DIR__.'/auth.php';
