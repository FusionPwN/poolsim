<?php

namespace App\Providers;

use App\Services\GameLogic;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AvatarServiceProvider extends ServiceProvider implements DeferrableProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->singleton(GameLogic::class, function ($app) {
			return new GameLogic();
		});
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array<int, string>
	 */
	public function provides(): array
	{
		return [GameLogic::class];
	}
}
