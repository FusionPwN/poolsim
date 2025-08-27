<?php

use App\Livewire\Settings\Api;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\TournamentsIndex;
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
        Route::get('/', TournamentsIndex::class)->name('tournaments.index');
    });
});

/* Route::get('/', TournamentsIndex::class)->name('tournaments.index');
Route::get('/tournaments/{tournament}', TournamentShow::class)->name('tournament.show');
Route::get('/matches/{match}', MatchShow::class)->name('match.show');
Route::get('/players/{player}', PlayerShow::class)->name('player.show'); */

require __DIR__.'/auth.php';
