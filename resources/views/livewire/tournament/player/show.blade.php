@php
    use App\Enums\TournamentStatus;
@endphp

<x-layouts.app.layout>
	<x-slot name="heading">
        <div class="flex flex-col gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <flux:icon name="trophy"/>
                    {{ $tournament->name }} 
                    <flux:badge icon="{{ match ($tournament->status) {
                        TournamentStatus::OPEN => 'check-circle',
                        TournamentStatus::ONGOING => 'loading',
                        TournamentStatus::ENDED => 'x-circle',
                    } }}" class="" size="sm" color="{{ match ($tournament->status) {
                        TournamentStatus::OPEN => 'teal',
                        TournamentStatus::ONGOING => 'orange',
                        TournamentStatus::ENDED => 'zinc',
                    } }}">{{ Str::title($tournament->status->value) }}</flux:badge>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if (!empty($player->avatar()))
                    <flux:avatar size="lg" circle src="{{ $player->avatar() }}" />
                @else
                    <flux:avatar size="lg" circle icon="user" />
                @endif
                {{ $player->name }}
            </div>
        </div>
	</x-slot>
	<x-slot name="subheading">
		<div class="flex justify-between items-end">
			<div class="flex items-center gap-2">
                <div class="flex flex-col">
					<span>Position</span>
					<flux:button icon="numbered-list" size="xs" variant="primary" class="h-7">{{ $tournament->getPlayerPosition($player) }}</flux:button>
				</div>
                <flux:separator vertical variant="subtle" />
                <div class="flex flex-col">
					<span>Points</span>
					<flux:button size="xs" variant="primary" class="h-7">{{ $tournament->players->where('id', $player->id)->first()->pivot->points }}</flux:button>
				</div>
                <flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Game wins</span>
					<flux:button size="xs" icon="trophy" variant="primary" color="amber" class="h-7">{{ $tournament->players->where('id', $player->id)->first()->pivot->wins }}</flux:button>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Games losses</span>
					<flux:button size="xs" icon="x-mark" variant="primary" color="red" class="h-7">{{ $tournament->players->where('id', $player->id)->first()->pivot->losses }}</flux:button>
				</div>
                <flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Fouls</span>
					<flux:button size="xs" variant="primary" color="orange" class="h-7">{{ $tournament->players->where('id', $player->id)->first()->pivot->fouls }}</flux:button>
				</div>
			</div>
		</div>
	</x-slot>

	<div class="flex items-start max-xl:flex-col gap-6 w-full">
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-5">
				Game in {{ $tournament->name }}
			</flux:heading>

			<div class="flex flex-col gap-3">
				@forelse ($this->games as $game)
					@livewire('tournament.game-item', ['game' => $game], key('game-' . $game->id))
				@empty
					<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
						No games to display.
					</div>
				@endforelse

				{{ $this->games->links('vendor.livewire.tailwind') }}
			</div>
		</div>
	</div>
</x-layouts.app.layout>

@script
	<script>
		Echo.private('tournaments.{{ $tournament->id }}')
			.listen('GamesGenerated', (e) => {
				$wire.refresh();
			})
			.listen('TournamentUpdated', (e) => {
				$wire.refresh();
			});
		
		Echo.private('games')
			.listen('GameFinished', (e) => {
				$wire.refresh();
			});
	</script>
@endscript