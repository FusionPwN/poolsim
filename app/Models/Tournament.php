<?php

namespace App\Models;

use App\Enums\TournamentStatus;
use App\Jobs\SetupTournamentJob;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TournamentStatus $status
 */
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
		return $this->belongsToMany(Player::class)
			->withPivot('points', 'wins', 'losses', 'fouls');
    }

	/**
	 * @return HasMany<Game, $this>
	 */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

	/**
	 * Gets the tournament winner checking if tournament already ended
	 * 
	 * @return ?Player
	 */
	public function winner(): ?Player
	{
		if ($this->status !== TournamentStatus::ENDED) {
			return null;
		}

		return $this->players()->wherePivot('points', '>', 0)->orderByPivot('points', 'desc')->first();
	}

	public static function new(string $name, int $player_count, bool $simulate): self
	{
		$tournament = new self();
		$tournament->fill([
			'name' 		=> $name,
			'status' 	=> TournamentStatus::OPEN,
		]);
		$tournament->save();

		if (app()->runningUnitTests()) {
			dispatch(new SetupTournamentJob($tournament, $player_count, $simulate));
		} else {
			dispatch(new SetupTournamentJob($tournament, $player_count, $simulate))
				->delay(now()->addSeconds(10));
		}

		return $tournament;
	}
}
