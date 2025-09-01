<div class="w-full">
    <flux:heading size="lg" level="1" class="mb-5">Scoreboard</flux:heading>
    <x-flux.table.table :paginate="$this->players">
        <x-flux.table.columns>
            <x-slot name="columns">
                <x-flux.table.column class="text-center" name="Position"/>
                <x-flux.table.column name="Name" />
                <x-flux.table.column class="text-center" name="Wins"/>
                <x-flux.table.column class="text-center" name="Losses"/>
                <x-flux.table.column class="text-center" name="Fouls"/>
                <x-flux.table.column class="text-center" name="Points"/>
            </x-slot>
        </x-flux.table.columns>
        <x-flux.table.rows>
            @forelse ($this->players as $player)
                <x-flux.table.row>
                    <x-flux.table.cell class="text-center font-black">{{ $player->position }}</x-flux.table.cell>
                    <x-flux.table.cell>
                        <div class="flex items-center">
                            <flux:button href="{{ route('tournament.players.show', [$tournament, $player]) }}" size="xs" variant="ghost">
                                @if (!empty($player->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $player->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $player->name }}
                            </flux:button>
                        </div>
                    </x-flux.table.cell>
                    <x-flux.table.cell class="text-center text-teal-500! dark:text-teal-400!">{{ $player->pivot->wins }}</x-flux.table.cell>
                    <x-flux.table.cell class="text-center text-red-500! dark:text-red-400!">{{ $player->pivot->losses }}</x-flux.table.cell>
                    <x-flux.table.cell class="text-center">{{ $player->pivot->fouls }}</x-flux.table.cell>
                    <x-flux.table.cell class="text-center font-black">{{ $player->pivot->points }}</x-flux.table.cell>
                </x-flux.table.row>
            @empty
                <x-flux.table.row>
                    <x-flux.table.cell colspan="6">
                        <div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
                            <flux:icon name="loading" class="text-zinc-400" />
                            Waiting for players to join...
                        </div>
                    </x-flux.table.cell>
                </x-flux.table.row>
            @endforelse
        </x-flux.table.row>
    </x-flux.table.table>
</div>

@script
    <script>
        Echo.private('tournaments.{{ $tournament->id }}')
            .listen('PlayersGenerated', (e) => {
				$wire.refresh();
			})
            .listen('GameFinished', (e) => {
				$wire.refresh();
			});
    </script>
@endscript