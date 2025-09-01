<div class="w-full">
    @if ($game->isScheduled() || $game->isOngoing())
        <flux:button.group>
            <flux:button :loading="false" href="{{ route('tournament.games.show', [$game->tournament, $game]) }}" class="flex flex-col w-full h-full p-6" variant="filled">
                <div class="grid grid-cols-5 gap-4 w-full">
                    <div class="col-start-0 col-span-2 flex justify-start">
                        <flux:badge icon="user" color="teal">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->player1->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->player1->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->player1->name }}
                            </div>
                        </flux:badge>
                    </div>
                    <div class="flex items-center justify-center">
                        vs
                    </div>
                    <div class="col-start-4 col-span-2 flex justify-end">
                        <flux:badge icon="user" color="teal">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->player2->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->player2->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->player2->name }}
                            </div>
                        </flux:badge>
                    </div>
                </div>
            </flux:button>
            @if ($game->isScheduled())
                <flux:button
                    variant="filled"
                    class="cursor-pointer h-full p-6"
                    style="height: auto;"
                    x-data="{ isSimulating: false }"
                    x-bind:disabled="isSimulating"
                    x-on:click.prevent="isSimulating = true; $wire.simulate()"
                >
                    <span class="flex items-center gap-2" x-show="!isSimulating">
                        <flux:icon name="play-circle" variant="solid"/>
                        Simulate
                    </span>
                    <span class="flex items-center gap-2" x-show="isSimulating">
                        <flux:icon name="loading" class="animate-spin mr-2" />
                        Simulating...
                    </span>
                </flux:button>
            @else
                <flux:button :loading="false" icon="loading" variant="filled" class="cursor-pointer h-full p-6" style="height: auto;" disabled>
                    <span class="ml-2 font-mono text-xs text-gray-500" x-text="timer" x-data="{
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
                    ></span>
                </flux:button>
            @endif
        </flux:button.group>
    @else
        <flux:button :loading="false" href="{{ route('tournament.games.show', [$game->tournament, $game]) }}" variant="filled" class="cursor-pointer flex flex-col w-full h-full p-6">
            <div class="grid grid-cols-5 gap-4 w-full">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <flux:badge icon="hand-thumb-up" color="amber">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->winner->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->winner->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->winner->name }}
                        </div>
                    </flux:badge>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    vs
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <flux:badge icon="hand-thumb-down" color="red">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->loser->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->loser->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->loser->name }}
                        </div>
                    </flux:badge>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-4 w-full mt-3">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <span>{{ $game->winner->skill }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Skill
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->loser->skill }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-3">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <span>{{ $game->getFoulsWinner() }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Fouls
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getFoulsLoser() }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-2">
                <div class="col-start-0 col-span-2">
                    <span>0</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Balls left
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getLosingBallsLeft() }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-2">
                <div class="col-start-0 col-span-2">
                    <span>{{ $game->getWinningBallType() }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Ball type
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getLosingBallType() }}</span>
                </div>
            </div>
        </flux:button>
    @endif
</div>

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