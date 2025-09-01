<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GameLogic
{
	/**
	 * Configurable points.
	 * 
	 * @var array<string, int>
	 */
	public array $points = [
		'win' => 3,
		'loss' => 1
	];

	/**
	 * Configurable chances for random events and fouls.
	 * 
	 * @var array<string, int>
	 */
	public array $chances = [
		'black_off_table' => 2,
		'failed_to_hit_own_balls' => 10,
		'potted_cue_ball' => 10,
		'cue_ball_off_table' => 5,
	];
	/**
	 * @var array<string, mixed>
	 */
	public array $balls;

	/**
	 * @var \Illuminate\Support\Collection<int, \App\Models\Player>
	 */
	public Collection $players;
	/**
	 * @var array<int, array{id: int, name: string, skill: int, group: string|null}>
	 */
	public array $playerData = [];
	public Game $game;

	/**
	 * @var array<int, array<string, mixed>>
	 */
	public array $actions = [];

	public int $breaker;
	public int $current;
	public int $opponent;
	public bool $lastCueBallInHand = false;
	public bool $isBreak = true;

	public bool $gameEnded = false;
	public int $winner;
	public int $loser;

	protected int $maxMultiPotCount = 2;
	
	public function __construct()
	{
		$this->resetBalls();
	}

	/**
	 * Reset the balls to their initial state.
	 *
	 * @return void
	 */
	public function resetBalls(): void
	{
		$this->balls = [
			'cue' => true,
			'black' => true,
			'solids' => array_fill(1, 7, true),
			'stripes' => array_fill(9, 7, true),
		];
	}

	/**
	 * Get balls by type or all balls.
	 *
	 * @param string|null $type
	 * @return array<int|string>
	 */
	public function getBalls(?string $type = null): array
	{
		if ($type === 'solids') {
			return array_keys(array_filter($this->balls['solids']));
		} else if ($type === 'stripes') {
			return array_keys(array_filter($this->balls['stripes']));
		} else if ($type === 'black') {
			return $this->balls['black'] ? [8] : [];
		} else if ($type === 'cue') {
			return $this->balls['cue'] ? ['cue'] : [];
		}

		return array_merge(
			array_keys(array_filter($this->balls['solids'])), 
			array_keys(array_filter($this->balls['stripes'])),
			[8],
			['cue'],
		);
	}

	/**
	 * @return array<int, array{id: int, name: string, skill: int, group: string|null}>
	 */
    public function getPlayerData(): array
	{
		return $this->playerData;
	}

	/**
	 * @param \Illuminate\Support\Collection<int, \App\Models\Player> $players
	 * @return void
	 */
	public function setPlayerData(Collection $players): void
	{
		if ($players->count() !== 2) {
			throw new \InvalidArgumentException('Exactly two players are required.');
		}

		$this->playerData = $players->map(function ($player) {
			return [
				'id' => $player->id,
				'name' => $player->name,
				'skill' => $player->skill,
				'group' => null,
			];
		})->toArray();
	}

	/**
	 * Set the game instance.
	 *
	 * @param Game $game
	 * @return void
	 */
	protected function setGame(Game $game): void
	{
		$this->game = $game;
	}


    /**
     * Get the configured chance value for a given event key.
     *
     * @param string $key
     * @return int
     */
    public function getChance(string $key): int
    {
        return $this->chances[$key] ?? 0;
    }

	/**
	 * Run the pool game simulation.
	 *
	 * @param Game $game
	 * @param \Illuminate\Support\Collection<int, \App\Models\Player> $players
	 * @return array{
 *     actions: array<int, array<string, mixed>>,
 *     total_balls_left: int,
 *     balls_left_by_type: array<string, int>,
 *     total_actions: int,
 *     total_fouls: int,
 *     fouls_by_player: array<int, int>,
 *     winning_ball_type: string|null
 * }
	 */
	public function runSimulation(Game $game, Collection $players): array
	{
		$this->setPlayerData($players);
		$this->setGame($game);

		// coin toss to select who breaks
		$this->breaker = rand(0, 1);
		$this->current = $this->breaker;
		$this->opponent = 1 - $this->breaker;

		$foulCounts = [
			$this->playerData[0]['id'] => 0,
			$this->playerData[1]['id'] => 0,
		];
		$totalFouls = 0;

		while (!$this->gameEnded) {
			$player = &$this->playerData[$this->current];
			$opp = &$this->playerData[$this->opponent];

			$this->balls['cue'] = true; // Reset cue ball each turn

			$action = [
				'player_id' => $player['id'],
				'player_name' => $player['name'],
				'skill' => $player['skill'],
				'foul' => false,
				'foul_reason' => null,
				'pots' => [],
				'cue_ball_in_hand' => $this->lastCueBallInHand,
				'group' => $player['group'] ?? null,
				'miss_reason' => null,
				'is_break' => $this->isBreak,
				'pot_chance' => null
			];

			$this->simulateShot($action, $player, $opp);
			$this->simulateFoul($action, $player, $opp);

			if (empty($action['foul_reason'])) {
				$this->isBreak = false;
			}

			$this->lastCueBallInHand = $action['foul'];

			if ($action['foul']) {
				$totalFouls++;
				$foulCounts[$player['id']]++;
			}

			if ($action['foul'] || count($action['pots']) === 0) {
				$this->current = $this->opponent;
				$this->opponent = 1 - $this->current;
			}

			$action['balls_remaining'] = [
				'solids' => implode(', ', array_keys(array_filter($this->balls['solids']))),
				'stripes' => implode(', ', array_keys(array_filter($this->balls['stripes']))),
				'black' => $this->balls['black'] ? 8 : '',
				'cue' => $this->balls['cue'] ? 'cue' : '',
			];

			$this->actions[] = $action;

			if (!$action['foul'] && in_array(8, $action['pots'])) {
				$this->gameEnded = true;
				$this->winner = $this->playerData[$this->current]['id'];
				$this->loser = $this->playerData[$this->opponent]['id'];
			}
		}

		$remainingBalls = $this->getRemainingBalls();
		$totalBallsLeft = count($remainingBalls['solids']) + count($remainingBalls['stripes']);

		$winnerData = collect($this->playerData)->firstWhere('id', $this->winner);
		$winningBallType = $winnerData['group'];

		$simulationResults = [
			'actions' => $this->actions,
			'total_balls_left' => $totalBallsLeft,
			'balls_left_by_type' => [
				'solids' => count($remainingBalls['solids']),
				'stripes' => count($remainingBalls['stripes'])
			],
			'total_actions' => count($this->actions),
			'total_fouls' => $totalFouls,
			'fouls_by_player' => $foulCounts,
			'winning_ball_type' => $winningBallType,
		];

		$this->saveGameResults($simulationResults);
		return $simulationResults;
	}

	/**
	 * Simulates a shot for the current player.
	 *
	 * @param array<string, mixed> $action
	 * @param array<string, mixed> $player
	 * @param array<string, mixed> $opp
	 * @return void
	 */
	public function simulateShot(array &$action, array &$player, array &$opp): void
	{
		$potChance = 0;
		$multiplePotChance = 0;

		if ($this->isBreak) {
			$potChance = $this->getBreakPotChance($player['skill']);
			$multiplePotChance = $this->getExtraBallsChance($player['skill']);
		} else {
			$potChance = $this->getPotChance($player['skill'], $this->lastCueBallInHand);
			$multiplePotChance = $this->getExtraBallsChance($player['skill']);
		}

		$roll = rand(1, 100);
		$multipleRoll = rand(1, 100);

		$action['pot_chance'] = $potChance;
		$action['multiple_pot_chance'] = $multiplePotChance;
		$action['pot_roll'] = $roll;
		$action['multiple_pot_roll'] = $multipleRoll;

		// potted a ball
		if ($roll <= $potChance) {
			// assign group based on pot type
			if ($player['group'] === null) {
				$potType = rand(0, 1) ? 'solids' : 'stripes';

				$player['group'] = $potType;
				$opp['group'] = $potType === 'solids' ? 'stripes' : 'solids';

				$remainingBalls = $this->getShuffledBalls();
			} else {
				$potType = count($this->getRemainingBalls()[$player['group']]) === 0 ? 'black' : $player['group'];
				$remainingBalls = $this->getShuffledBalls($potType, 1);
			}

			// tag ball as potted
			$ballNum = $remainingBalls[0];
			if ($potType == 'black') {
				$this->balls['black'] = false;
			} else {
				$this->balls[$potType][$ballNum] = false;
			}
			$action['pots'][] = $ballNum;

			// only able to multipot with more than 1 ball in the field not counting the cue ball, that chance is calculated in the fouls section
			if ($multipleRoll <= $multiplePotChance && count($remainingBalls) - 1 > 1) {
				$extraPot = $this->isBreak ? rand(1, 3) : 1;
				for ($i = 0; $i < $extraPot; $i++) {
					$remainingBalls = $this->getShuffledBalls();
					$ballNum = $remainingBalls[0];
					$potType = $this->getBallTypeFromNumber($ballNum);
					if ($potType == 'black') {
						$this->balls['black'] = false;
					} else {
						$this->balls[$potType][$ballNum] = false;
					}
					$action['pots'][] = $ballNum;
				}
			}
		} else {
			$action['miss_reason'] = $this->isBreak ? 'no_ball_potted_on_break' : 'no_ball_potted';
		}

		$this->lastCueBallInHand = false;
	}

	/**
	 * Handles fouls for the current shot.
	 *
	 * @param array<string, mixed> $action
	 * @param array<string, mixed> $player
	 * @param array<string, mixed> $opp
	 * @return void
	 */
	public function simulateFoul(array &$action, array $player, array $opp): void
	{
		// if black is potted on break, then the game is restarted
		if (in_array(8, $action['pots']) && $this->isBreak) {
			$this->resetBalls();
			$action['foul'] = false; // set foul as false so it doesnt switch players
			$action['foul_reason'] = 'potted_8_ball_on_break';
			return;
		}
		
		// potted 8 ball illegaly
		if (in_array(8, $action['pots']) && $player['group'] !== null && count($this->getRemainingBalls()[$player['group']]) > 0) {
			$this->balls['black'] = false;
			$action['foul'] = true;
			$action['foul_reason'] = 'potted_8_ball_illegally';
			$this->winner = $opp['id'];
			$this->loser = $player['id'];
			$this->gameEnded = true;
			return;
		}

		// Configurable chance to knock 8 ball off table (instant loss)
		if (rand(1, 100) <= $this->getChance('black_off_table')) {
			$this->balls['black'] = false;
			$action['foul'] = true;
			$action['foul_reason'] = 'black_off_table';
			$this->winner = $opp['id'];
			$this->loser = $player['id'];
			$this->gameEnded = true;
			return;
		}

		if (count($action['pots']) > 0 && $this->pottedBallsBelongsToOpponent($action['pots'])) {
			$action['foul'] = true;
			$action['foul_reason'] = 'potted_opponents_ball';
			return;
		}

		// Separate roll for each foul type
		if (count($action['pots']) === 0 && rand(1, 100) <= $this->getChance('failed_to_hit_own_balls')) {
			$action['foul'] = true;
			$action['foul_reason'] = 'failed_to_hit_own_balls';
			return;
		}

		if (rand(1, 100) <= $this->getChance('potted_cue_ball')) {
			$action['foul'] = true;
			$action['foul_reason'] = 'potted_cue_ball';
			$this->balls['cue'] = false;
			if (in_array(8, $action['pots'])) {
				$action['foul_reason'] = 'potted_cue_ball_and_black';
				$this->winner = $opp['id'];
				$this->loser = $player['id'];
				$this->gameEnded = true;
			}
			return;
		}

		if (rand(1, 100) <= $this->getChance('cue_ball_off_table')) {
			$action['foul'] = true;
			$action['foul_reason'] = 'cue_ball_off_table';
			$this->balls['cue'] = false;
			if (in_array(8, $action['pots'])) {
				$action['foul_reason'] = 'potted_black_and_cue_ball_off_table';
				$this->winner = $opp['id'];
				$this->loser = $player['id'];
				$this->gameEnded = true;
			}
			return;
		}
	}

	/**
	 * Get the type of ball from its number.
	 *
	 * @param int|string $number
	 * @return string
	 */
	public function getBallTypeFromNumber(int|string $number): string
	{
		return $this->isBallSolids($number) ? 'solids' : ($this->isBallStripes($number) ? 'stripes' : ($number == 8 ? 'black' : 'unknown'));
	}

	/**
	 * Checks if any potted balls belong to the opponent.
	 *
	 * @param array<int|mixed> $balls
	 * @return bool
	 */
	public function pottedBallsBelongsToOpponent(array $balls): bool
	{
		foreach ($balls as $ball) {
			if ($this->isOpponentsBall($ball)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if a ball belongs to the opponent.
	 *
	 * @param int|string $number
	 * @return bool
	 */
	public function isOpponentsBall(int|string $number): bool
	{
		$player = &$this->playerData[$this->current];
		$opp = &$this->playerData[$this->opponent];

		if ($player['group'] === 'solids') {
			return $opp['group'] === 'stripes' && in_array($number, array_keys($this->balls['stripes']));
		} else if ($player['group'] === 'stripes') {
			return $opp['group'] === 'solids' && in_array($number, array_keys($this->balls['solids']));
		}

		return false;
	}

	/**
	 * Checks if a ball belongs to the current player.
	 *
	 * @param int $number
	 * @return bool
	 */
	public function isOwnBall(int $number): bool
	{
		$player = $this->playerData[$this->current];

		if ($player['group'] === 'solids') {
			return in_array($number, array_keys($this->balls['solids']));
		} else {
			return in_array($number, array_keys($this->balls['stripes']));
		}
	}

	/**
	 * Checks if a ball number is a solid.
	 *
	 * @param int|string $number
	 * @return bool
	 */
	public function isBallSolids(int|string $number): bool
	{
		return in_array($number, array_keys($this->balls['solids']));
	}

	/**
	 * Checks if a ball number is a stripe.
	 *
	 * @param int|string $number
	 * @return bool
	 */
	public function isBallStripes(int|string $number): bool
	{
		return in_array($number, array_keys($this->balls['stripes']));
	}

	/**
	 * Get remaining balls by type.
	 *
	 * @return array<string, mixed>
	 */
	public function getRemainingBalls(): array
	{
		return [
			'solids' => array_keys(array_filter($this->balls['solids'])),
			'stripes' => array_keys(array_filter($this->balls['stripes'])),
			'black' => $this->balls['black'] ? 8 : '',
			'cue' => $this->balls['cue'] ? 'cue' : '',
		];
	}

	/**
	 * Get a shuffled list of balls, with optional prioritization to simulate aiming for a specific ball type
	 * 
	 * @param string|null $priority 'solids', 'stripes', or 'black'
	 * @param int $count Number of balls to move to the front (default 0)
	 * @return array<int|string>
	 */
	public function getShuffledBalls(?string $priority = null, int $count = 0): array
	{
		$remainingBalls = $this->getRemainingBalls();

		$allBalls = array_merge($remainingBalls['solids'], $remainingBalls['stripes'], [$remainingBalls['black']]);
		shuffle($allBalls);

		if ($priority !== null && $count > 0) {
			$priorityBalls = [];
			if ($priority === 'solids') {
				$count = min($count, count($remainingBalls['solids'])); # limit count to available balls
				$priorityBalls = array_slice($remainingBalls['solids'], 0, $count);
			} elseif ($priority === 'stripes') {
				$count = min($count, count($remainingBalls['stripes'])); # limit count to available balls
				$priorityBalls = array_slice($remainingBalls['stripes'], 0, $count);
			} elseif ($priority === 'black') {
				$count = 1; # limit count to available balls
				$priorityBalls = [8];
			}

			// Remove priority balls from shuffled array and add to front
			foreach (array_reverse($priorityBalls) as $ball) {
				$key = array_search($ball, $allBalls, true);
				if ($key !== false) {
					array_splice($allBalls, $key, 1);
					array_unshift($allBalls, $ball);
				}
			}
		}

		return $allBalls;
	}

	/**
	 * Save the game results with all simulation fields.
	 *
	 * @param array{
	 *     winning_ball_type: string|null,
	 *     actions: array<int, array<string, mixed>>,
	 *     total_balls_left: int,
	 *     balls_left_by_type: array<string, int>,
	 *     total_actions: int,
	 *     total_fouls: int,
	 *     fouls_by_player: array<int, int>
	 * } $simulationResults
	 * @return void
	 */
	protected function saveGameResults(array $simulationResults): void
	{

		$this->game->update([
			'winner_id'         => $this->winner,
			'loser_id'          => $this->loser,
			'winning_ball_type' => $simulationResults['winning_ball_type'],
			'actions'           => $simulationResults['actions'],
			'balls_left_solids' => $simulationResults['balls_left_by_type']['solids'],
			'balls_left_stripes'=> $simulationResults['balls_left_by_type']['stripes'],
			'total_actions'     => $simulationResults['total_actions'],
			'total_fouls'       => $simulationResults['total_fouls'],
			'fouls_player1'     => $simulationResults['fouls_by_player'][$this->winner === $this->game->player1_id ? $this->winner : $this->loser],
			'fouls_player2'     => $simulationResults['fouls_by_player'][$this->winner === $this->game->player2_id ? $this->winner : $this->loser]
		]);

		// Update points on the tournament_player pivot table
		$tournament = $this->game->tournament;
		$winnerId = $this->winner;
		$loserId = $this->loser;

		// Add points, wins, losses, fouls for winner and loser
		$tournament->players()->updateExistingPivot($winnerId, [
			'points' => DB::raw('points + ' . $this->points['win']),
			'wins'   => DB::raw('wins + 1'),
			'fouls'  => DB::raw('fouls + ' . ($simulationResults['fouls_by_player'][$winnerId] ?? 0)),
		]);
		$tournament->players()->updateExistingPivot($loserId, [
			'points'  => DB::raw('points + ' . $this->points['loss']),
			'losses'  => DB::raw('losses + 1'),
			'fouls'   => DB::raw('fouls + ' . ($simulationResults['fouls_by_player'][$loserId] ?? 0)),
		]);
	}

	/**
	 * Get the chance of potting a ball.
	 *
	 * @param int $skill
	 * @param bool $cueBallInHand
	 * @return int
	 */
	protected function getPotChance(int $skill, bool $cueBallInHand): int
	{
		$base = $cueBallInHand ? 90 : 65;
		return min($base + intdiv($skill, 2), 99);
	}

	/**
	 * Get the chance of potting an extra ball.
	 *
	 * @param int $skill
	 * @return int
	 */
	protected function getExtraBallsChance(int $skill): int
	{
		$base = min($this->isBreak ? rand(1, 3) : 1, intdiv($skill, 10)); // 0-5
		$max = 20;
		$diff = $max - $base;

		// 3 points are linear with skill, rest (diff - 3) are random
		// bonus only applies for skill >= 20, so skill 0 gives 0
		$linear = min(intdiv($skill, 20), 3);
		$random = ($diff > 3 && $linear > 0) ? rand(0, $diff - 3) : 0;

		return min($base + $linear + $random, $max);
	}

	/**
	 * Get the chance of potting a ball while breaking.
	 *
	 * @param int $skill
	 * @return int
	 */
	protected function getBreakPotChance(int $skill): int
	{
		return min(60 + intdiv($skill, 3), 99);
	}

	/**
	 * Create games for a tournament in a round-robin format this way every player faces each other once.
	 *
	 * @param Tournament $tournament
	 * @return Collection<int, Game>
	 */
	public function createGames(Tournament $tournament): Collection
	{
		$games = collect();
		$players = $tournament->players()->pluck('players.id')->toArray();
		$sequence = 1;

		$gamesData = [];
		for ($i = 0; $i < count($players); $i++) {
			for ($x = $i + 1; $x < count($players); $x++) {
				$player1 = $players[$i];
				$player2 = $players[$x];

				// Optionally check for existing games
				$exists = Game::where('tournament_id', $tournament->id)
					->where('player1_id', $player1)
					->where('player2_id', $player2)
					->exists();

				if (!$exists) {
					$gamesData[] = [
						'tournament_id' => $tournament->id,
						'player1_id' => $player1,
						'player2_id' => $player2,
						'sequence' => $sequence++,
						'status' => GameStatus::SCHEDULED,
						'created_at' => now(),
						'updated_at' => now(),
					];
				}
			}
		}

		DB::beginTransaction();
		try {
			Game::insert($gamesData);
			DB::commit();
		} catch (\Illuminate\Database\QueryException $e) {
			// Duplicate key exception, rollback and continue
			DB::rollBack();
		}

		// Return the games for this tournament
		return $tournament->games()->get();
	}

	/**
	 * Split actions into turns and describe each turn.
	 *
	 * @param Game $game
	 * @return array<int, array{player_id: int, player_name: string, actions: array<int, array<string, mixed>>, description: string}>
	 */
	public function describe(Game $game): array
	{
		$actions = $game->actions;
		if (!is_array($actions) || empty($actions)) {
			return [];
		}

		$turns = [];
		$currentPlayerId = null;
		$currentPlayerName = null;
		$currentTurnActions = [];

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
					'player_id' => $currentPlayerId,
					'player_name' => $currentPlayerName,
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
				'player_id' => $currentPlayerId,
				'player_name' => $currentPlayerName,
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
	 * @return array<int, string>
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
					$type = $this->getBallTypeFromNumber($ballNum);
					$balls[] = "$ballNum $type";
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
}