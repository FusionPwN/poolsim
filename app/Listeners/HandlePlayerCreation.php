<?php

namespace App\Listeners;

use App\Events\PlayerCreated;
use App\Jobs\ProcessAvatarJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandlePlayerCreation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PlayerCreated $event): void
    {
		dispatch(new ProcessAvatarJob($event->player));
    }
}
