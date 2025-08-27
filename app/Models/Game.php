<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
	protected $fillable = ['tournament_id', 'winner_id', 'loser_id', 'balls_left', 'log', 'status'];

	protected function casts(): array
	{
		return [
			'status' 	=> GameStatus::class,
		];
	}

	/**
	 * @return BelongsTo<Tournament, $this>
	 */
	public function tournament(): BelongsTo
	{
		return $this->belongsTo(Tournament::class);
	}

	/**
	 * @return BelongsTo<Player, $this>
	 */
	public function winner(): BelongsTo
	{
		return $this->belongsTo(Player::class, 'winner_id');
	}

	/**
	 * @return BelongsTo<Player, $this>
	 */
	public function loser(): BelongsTo
	{
		return $this->belongsTo(Player::class, 'loser_id');
	}
}
