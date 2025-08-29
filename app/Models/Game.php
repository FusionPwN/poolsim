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
	public function player1(): BelongsTo
	{
		return $this->belongsTo(Player::class, 'player1_id');
	}

	/**
	 * @return BelongsTo<Player, $this>
	 */
	public function player2(): BelongsTo
	{
		return $this->belongsTo(Player::class, 'player2_id');
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

	/**
	 * @return bool
	 */
	public function player1Wins(): bool
	{
		return $this->winner_id === $this->player1_id;
	}

	/**
	 * @return bool
	 */
	public function player2Wins(): bool
	{
		return $this->winner_id === $this->player2_id;
	}
}
