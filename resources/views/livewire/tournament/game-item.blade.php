<flux:callout inline wire:key="game-{{ $game->id }}">
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
            <flux:button :loading="false" icon="loading" disabled>
            </flux:button>
        @elseif ($game->isEnded())
            <flux:button href="{{ route('tournament.games.show', [$game->tournament, $game]) }}" :loading="false" icon="document-magnifying-glass" class="cursor-pointer">
                Match details
            </flux:button>
        @endif
    </x-slot>
</flux:callout>

@script
    <script>

    </script>
@endscript