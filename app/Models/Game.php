<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Jobs\GameSimulationJob;
use App\Services\GameLogic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property GameStatus $status
 */

class Game extends Model
{
	protected $fillable = ['tournament_id', 'winner_id', 'loser_id', 'balls_left', 'log', 'status', 'started_at', 'ended_at'];

	protected function casts(): array
	{
		return [
			'status'     	=> GameStatus::class,
			'actions'		=> 'json',
			'started_at' 	=> 'datetime',
			'ended_at'   	=> 'datetime',
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
	 * Get all players involved in the game.
	 *
	 * @return \Illuminate\Support\Collection<int, \App\Models\Player>
	 */
	public function players(): Collection /* @phpstan-return Collection<int, Player> */
	{
		return collect([$this->player1, $this->player2]);
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

	/**
	 * @return bool
	 */
	public function hasWinner(): bool
	{
		return $this->winner_id !== null;
	}

	/**
	 * @return bool
	 */
	public function isScheduled(): bool
	{
		return $this->status === GameStatus::SCHEDULED;
	}

	/**
	 * @return bool
	 */
	public function isOngoing(): bool
	{
		return $this->status === GameStatus::ONGOING;
	}

	/**
	 * @return bool
	 */
	public function isEnded(): bool
	{
		return $this->status === GameStatus::ENDED;
	}

	public function setAsScheduled(): void
	{
		$this->update(['status' => GameStatus::SCHEDULED]);
	}

	public function setAsOngoing(): void
	{
		$this->update(['status' => GameStatus::ONGOING, 'started_at' => now()]);
	}

	public function setAsEnded(): void
	{
		$this->update(['status' => GameStatus::ENDED, 'ended_at' => now()]);
	}

	public function simulate(): void
	{
		dispatch((new GameSimulationJob($this))->onQueue('game-simulation'));
	}

	public function getWinningBallType(): ?string
	{
		return $this->winning_ball_type;
	}

	public function getLosingBallType(): ?string
	{
		if ($this->winning_ball_type === null) {
			return null;
		}

		return $this->winning_ball_type === 'solids' ? 'stripes' : 'solids';
	}

	public function getLosingBallsLeft(): ?int
	{
		if ($this->winning_ball_type === null) {
			return null;
		}

		return $this->{'balls_left_' . $this->getLosingBallType()};
	}

	public function getFoulsWinner(): ?int
	{
		return $this->winner_id === $this->player1_id ? $this->fouls_player1 : $this->fouls_player2;
	}

	public function getFoulsLoser(): ?int
	{
		return $this->winner_id === $this->player1_id ? $this->fouls_player2 : $this->fouls_player1;
	}

	/**
	 * Split actions into turns and describe each turn.
	 *
	 * @return list<array{player_id: int, player_name: string, actions: non-empty-array<int<0, max>, mixed>}>
	 */
	public function describe(): array
	{
		$actions = $this->actions;
		if (empty($actions)) {
			return [];
		}

		$turns = [];
		$currentPlayerId = null;
		$currentPlayerName = null;
		$currentTurnActions = [];

		/** @phpstan-ignore-next-line */
		foreach ($actions as $action) {
			if ($currentPlayerId === null) {
				$currentPlayerId = $action['player_id'];
				$currentPlayerName = $action['player_name'] ?? '';
			}

			// If player changes, start a new turn
			if ($action['player_id'] !== $currentPlayerId && count($currentTurnActions) > 0) {
				$allDescriptions = $this->describeTurn($currentTurnActions, $currentPlayerName);
				foreach ($currentTurnActions as $i => $act) {
					$currentTurnActions[$i]['descriptions'] = $allDescriptions[$i] ?? [];
				}
				$turns[] = [
					'player_id' => (int) $currentPlayerId,
					'player_name' => (string) $currentPlayerName,
					'actions' => $currentTurnActions,
				];
				$currentTurnActions = [];
				$currentPlayerId = $action['player_id'];
				$currentPlayerName = $action['player_name'] ?? '';
			}
			$currentTurnActions[] = $action;
		}
		// Add last turn
		if (count($currentTurnActions) > 0) {
			$allDescriptions = $this->describeTurn($currentTurnActions, $currentPlayerName);
			foreach ($currentTurnActions as $i => $act) {
				$currentTurnActions[$i]['descriptions'] = $allDescriptions[$i] ?? [];
			}
			$turns[] = [
				'player_id' => (int) $currentPlayerId,
				'player_name' => (string) $currentPlayerName,
				'actions' => $currentTurnActions,
			];
		}
		return $turns;
	}

	/**
	 * Describe a turn based on its actions and player name.
	 *
	 * @param array<int, array<string, mixed>> $actions
	 * @param string $playerName
	 * @return list<list<string>>
	 */
	protected function describeTurn(array $actions, string $playerName): array
	{
		$descriptions = [];
		foreach ($actions as $action) {
			$actionDescriptions = [];
			if (!empty($action['is_break'])) {
				$actionDescriptions[] = "Break shot to start the turn.";
			}
			if (!empty($action['pots'])) {
				$balls = [];
				foreach ($action['pots'] as $ballNum) {
					$type = app(GameLogic::class)->getBallTypeFromNumber($ballNum);
					$balls[] = "$ballNum ($type)";
				}
				$actionDescriptions[] = "Potted ball(s): " . implode(', ', $balls) . ".";
			}
			if (!empty($action['foul'])) {
				$reason = $this->friendlyFoulReason($action['foul_reason'] ?? 'unknown');
				$actionDescriptions[] = "Foul committed: $reason.";
			}
			if (!empty($action['miss_reason'])) {
				$reason = $this->friendlyMissReason($action['miss_reason']);
				$actionDescriptions[] = "Missed shot: $reason.";
			}
			$descriptions[] = $actionDescriptions;
		}
		return $descriptions;
	}

	/**
	 * Convert foul reason to a friendly format.
	 */
	protected function friendlyFoulReason(string $reason): string
	{
		return match ($reason) {
			'potted_8_ball_on_break' => 'Potted the 8 ball on the break (game restarted)',
			'potted_8_ball_illegally' => 'Potted the 8 ball before clearing all group balls',
			'black_off_table' => 'Knocked the 8 ball off the table (instant loss)',
			'potted_opponents_ball' => 'Potted an opponent’s ball',
			'failed_to_hit_own_balls' => 'Failed to hit own group of balls',
			'potted_cue_ball' => 'Potted the cue ball',
			'potted_cue_ball_and_black' => 'Potted both cue ball and 8 ball',
			'cue_ball_off_table' => 'Knocked the cue ball off the table',
			'potted_black_and_cue_ball_off_table' => 'Knocked both cue ball and 8 ball off the table',
			default => ucfirst(str_replace('_', ' ', $reason)),
		};
	}

	/**
	 * Convert miss reason to a friendly format.
	 */
	protected function friendlyMissReason(string $reason): string
	{
		return match ($reason) {
			'no_ball_potted_on_break' => 'No ball potted on the break',
			'no_ball_potted' => 'No ball potted',
			default => ucfirst(str_replace('_', ' ', $reason)),
		};
	}

	/**
	 * Scope a query to only include scheduled games.
	 * @param Builder<Game> $query
	 * @return Builder<Game>
	 */
	public function scopeScheduled(Builder $query): Builder
	{
		return $query->where('status', GameStatus::SCHEDULED);
	}

	/**
	 * Scope a query to only include ongoing games.
	 * @param Builder<Game> $query
	 * @return Builder<Game>
	 */
	public function scopeOngoing(Builder $query): Builder
	{
		return $query->where('status', GameStatus::ONGOING);
	}

	/**
	 * Scope a query to only include ended games.
	 * @param Builder<Game> $query
	 * @return Builder<Game>
	 */
	public function scopeEnded(Builder $query): Builder
	{
		return $query->where('status', GameStatus::ENDED);
	}
}