<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avatar extends Model
{
	protected $fillable = ['player_id', 'size', 'path'];

	/**
	 * @return BelongsTo<Player, $this>
	 */
	public function player(): BelongsTo
	{
		return $this->belongsTo(Player::class);
	}
}
