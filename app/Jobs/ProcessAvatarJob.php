<?php

namespace App\Jobs;

use App\Models\Player;
use App\Services\AvatarApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAvatarJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Player $player)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AvatarApi $avatar_api): void
    {
        $results = $avatar_api->generate($this->player->gender, $this->player->name);

		$this->player->update([
			'avatar_original' => $results['original']['path'],
			'avatar_processed' => $results['640_360']['path'],
		]);
    }
}
