<?php

namespace App\Models;

use App\Enums\TournamentStatus;
use App\Events\PlayerCreated;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Player extends Model
{
	/** @use HasFactory<PlayerFactory> */
	use HasFactory;

	protected static string $factory = PlayerFactory::class;

    // Runtime property
    public ?string $group = null;

	/**
	 * @var array<string, string>
	 */
	protected $dispatchesEvents = [
		'created' => PlayerCreated::class,
	];
	
    protected $fillable = ['name', 'avatar_original', 'avatar_processed'];

	/**
	 * @return BelongsToMany<Tournament, $this>
	 */
    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class)->withPivot('points');
    }

	/**
	 * @return HasMany<Game, $this>
	 */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_id')
            ->orWhere('loser_id', $this->id);
    }

	/**
	 * @return HasMany<Avatar, $this>
	 */
	public function avatars(): HasMany
	{
		return $this->hasMany(Avatar::class);
	}

	/**
	 * Scope: Only players not in any ongoing tournament.
	 *  
	 * @param \Illuminate\Database\Eloquent\Builder<Player> $query
	 */
	public function scopeNotInOngoingTournament(\Illuminate\Database\Eloquent\Builder $query): void
	{
		$query->whereDoesntHave('tournaments', function ($q) {
			$q->where('status', TournamentStatus::ONGOING);
		});
	}

	/**
	 * Scope: Only players not in any tournament at all.
	 * 
	 * @param \Illuminate\Database\Eloquent\Builder<Player> $query
	 */
	public function scopeNotInAnyTournament(\Illuminate\Database\Eloquent\Builder $query): void
	{
		$query->whereDoesntHave('tournaments');
	}

	/**
	 * Fetch the avatar URL for the player.
	 * @return string
	 */
	public function avatar(): string
	{
		if (empty($this->avatar_processed)) {
			return '';
		}

		$url = Storage::disk('public')->url($this->avatar_processed);

		return $url;
	}
}
