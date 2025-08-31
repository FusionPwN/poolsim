<flux:callout inline>
    <flux:callout.heading>
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-3">
                @if ($game->hasWinner())
                    <flux:badge icon="hand-thumb-up" color="amber">{{ $game->winner->name }}</flux:badge>
                    vs
                    <flux:badge icon="hand-thumb-down" color="red">{{ $game->loser->name }}</flux:badge>
                @else
                    <flux:badge icon="user" color="teal">{{ $game->player1->name }}</flux:badge>
                    vs
                    <flux:badge icon="user" color="teal">{{ $game->player2->name }}</flux:badge>
                @endif
            </div>
        </div>
    </flux:callout.heading>

    @if ($game->isEnded())
        <flux:callout.text>
            <div class="flex items-center gap-2">
                <span>{{ $game->player1->name }} - Fouls: {{ $game->fouls_player1 }}</span>
                <flux:separator vertical variant="subtle" />
                <span>{{ $game->player2->name }} - Fouls: {{ $game->fouls_player2 }}</span>
                <flux:separator vertical variant="subtle" />
                <span>{{ $game->loser->name }} - Balls left: {{ $game->getLosingBallsLeft() }} ({{ $game->getLosingBallType() }})</span>
            </div>
        </flux:callout.text>
    @endif

    <x-slot name="actions" class="h-full">
        @if ($game->isScheduled())
            <flux:button :loading="false" icon="play-circle" wire:click="simulate()">
                Simulate
            </flux:button>
        @elseif ($game->isOngoing())
            <div class="flex items-center gap-2"
                x-data="{
                    startedAt: Number({{ optional($game->started_at)?->timestamp ?? 'null' }}),
                    finishedAt: Number({{ optional($game->finished_at)?->timestamp ?? 'null' }}),
                    isOngoing: {{ $game->isOngoing() ? 'true' : 'false' }},
                    elapsed: 0,
                    timer: '0:00',
                    interval: null,
                    debugLog(msg) { console.log('GameItem Alpine:', msg); },
                    updateTimer() {
                        this.elapsed++;
                        const m = Math.floor(this.elapsed / 60);
                        const s = this.elapsed % 60;
                        this.timer = `${m}:${s.toString().padStart(2, '0')}`;
                        if (this.elapsed > 8) {
                            this.debugLog('Timer > 8s, calling $wire.refresh()');
                            $wire.refresh();
                        }
                    },
                    start() {
                        this.debugLog('start() called, startedAt=' + this.startedAt);
                        if (!this.startedAt || !this.isOngoing) return;
                        this.debugLog('start() running, elapsed=' + (Math.floor(Date.now() / 1000) - this.startedAt));
                        this.elapsed = Math.floor(Date.now() / 1000) - this.startedAt;
                        this.updateTimer();
                        if (!this.interval) {
                            this.interval = setInterval(() => { this.updateTimer() }, 1000);
                        }
                    },
                    stop() {
                        this.debugLog('stop() called');
                        if (this.interval) { clearInterval(this.interval); this.interval = null; }
                    },
                    checkStatus() {
                        this.debugLog('checkStatus() called: isOngoing=' + this.isOngoing + ', startedAt=' + this.startedAt + ', finishedAt=' + this.finishedAt);
                        if (this.isOngoing && this.startedAt > 0) {
                            this.start();
                        } else {
                            this.stop();
                        }
                    }
                }"
                x-init="$nextTick(() => { checkStatus() })"
                @game-started.window="startedAt = Number($event.detail.startedAt); isOngoing = true; checkStatus()"
                @game-finished.window="finishedAt = Number($event.detail.finishedAt); isOngoing = false; checkStatus()"
            >
                <flux:button :loading="false" icon="loading" disabled>
                    <span class="ml-2 font-mono text-xs text-gray-500" x-text="timer"></span>
                </flux:button>
            </div>
        @elseif ($game->isEnded())
            <flux:button href="{{ route('tournament.games.show', [$game->tournament, $game]) }}" :loading="false" icon="document-magnifying-glass" class="cursor-pointer">
                Match details
            </flux:button>
        @endif
    </x-slot>
</flux:callout>

@script
    <script>
        Echo.private('games.{{ $game->id }}')
            .listen('GameStarted', (e) => {
                $wire.refresh();
            })
            .listen('GameFinished', (e) => {
                $wire.refresh();
            });
    </script>
@endscript