<?php

namespace App\Models;

use App\Enums\TournamentStatus;
use App\Jobs\SetupTournamentJob;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
	/** @use HasFactory<TournamentFactory> */
	use HasFactory;

	protected static string $factory = TournamentFactory::class;

    protected $fillable = ['name', 'status'];

	protected function casts(): array
	{
		return [
			'status' => TournamentStatus::class,
		];
	}

	/**
	 * @return BelongsToMany<Player, $this>
	 */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)->withPivot('points');
    }

	/**
	 * @return HasMany<Game, $this>
	 */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

	public static function new(string $name, int $player_count): self
	{
		$tournament = new self();
		$tournament->fill([
			'name' 		=> $name,
			'status' 	=> TournamentStatus::OPEN,
		]);
		$tournament->save();

		dispatch(new SetupTournamentJob($tournament, $player_count));
		
		return $tournament;
	}
}
