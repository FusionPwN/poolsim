<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 */
	public function register(): void
	{
		
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		// # HOUSE CLEANING

		Schema::defaultStringLength(191);								// Set default string length for database columns
		DB::prohibitDestructiveCommands($this->app->isProduction()); 	// Prevent destructive commands in production
		Model::unguard();												// Disable mass assignment protection globally
		// URL::forceScheme('https');									// Force HTTPS scheme for URLs
		Vite::usePrefetchStrategy('aggressive');						// Use aggressive prefetch strategy for Vite assets

		// # HOUSE CLEANING
	}
}
