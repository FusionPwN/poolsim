<?php

namespace App\Models;

use App\Enums\TournamentStatus;
use App\Events\TournamentUpdated;
use App\Jobs\SetupTournamentJob;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property TournamentStatus $status
 */
class Tournament extends Model
{
	/** @use HasFactory<TournamentFactory> */
	use HasFactory;

	protected static string $factory = TournamentFactory::class;

    protected $fillable = ['name', 'status'];

	protected static function booted(): void
	{
		static::updating(function ($model) {
			if ($model->isDirty('status')) {
				broadcast(new TournamentUpdated($model));
			}
		});
	}

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
	 * @return HasOne<Player>
	 */
	public function winner(): HasOne
	{
		return $this->hasOne(Player::class, 'id', 'winner_id');
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
			dispatch(new SetupTournamentJob($tournament, $player_count, $simulate))->onQueue('setup-tournament');
		} else {
			dispatch(new SetupTournamentJob($tournament, $player_count, $simulate))
				->onQueue('setup-tournament')
				->delay(now()->addSeconds(10));
		}

		return $tournament;
	}

	public function setAsOpen(): void
	{
		$this->update(['status' => TournamentStatus::OPEN]);
	}

	public function setAsOngoing(): void
	{
		$this->update(['status' => TournamentStatus::ONGOING]);
	}

	public function setAsEnded(): void
	{
		$this->update(['status' => TournamentStatus::ENDED]);
	}

	public function isOpen(): bool
	{
		return $this->status === TournamentStatus::OPEN;
	}

	public function isOngoing(): bool
	{
		return $this->status === TournamentStatus::ONGOING;
	}

	public function isEnded(): bool
	{
		return $this->status === TournamentStatus::ENDED;
	}

	/**
	 * Get the position (rank) of a player in this tournament.
	 *
	 * @param Player $player
	 * @return int|null
	 */
	public function getPlayerPosition(Player $player): ?int
	{
		$players = $this->players()
			->orderByDesc('pivot_points')
			->orderByDesc('pivot_wins')
			->orderBy('pivot_fouls')
			->get();

		foreach ($players as $i => $p) {
			if ($p->id === $player->id) {
				return $i + 1;
			}
		}
		return null;
	}
}
