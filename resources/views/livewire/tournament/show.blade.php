@php
    use App\Enums\TournamentStatus;
@endphp

<x-layouts.app.layout>
	<x-slot name="heading">
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
	</x-slot>
	<x-slot name="subheading">
		<div class="flex justify-between items-end">
			<div class="flex items-center gap-2">
				<div class="flex flex-col">
					<span>Creation date</span>
					<flux:badge icon="calendar-days">{{ $tournament->created_at->format('d-m-Y H:i') }}</flux:badge>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Players</span>
					<flux:badge icon="user-group">{{ $tournament->players->count() }}</flux:badge>
				</div>
				@if ($tournament->winner)
					<flux:separator vertical variant="subtle" />
					<div class="flex flex-col">
						<span>Winner</span>
						<flux:button href="{{ route('player.show', $tournament->winner) }}" size="xs" icon="trophy" variant="primary" color="amber" class="h-7">
							<div class="flex items-center gap-2">
								@if (!empty($tournament->winner->avatar()))
									<flux:avatar size="xs" circle src="{{ $tournament->winner->avatar() }}" />
								@else
									<flux:avatar size="xs" circle icon="user" />
								@endif
								{{ $tournament->winner->name }}
							</div>
						</flux:button>
					</div>
				@endif
			</div>
			<div>
				@if ($tournament->players->count() > 0 && $tournament->status === TournamentStatus::OPEN)
					<flux:button :loading="false" icon="play-circle">
						Simulate all
					</flux:button>
				@endif
			</div>
		</div>
	</x-slot>

	<div class="flex items-start max-md:flex-col gap-6 w-full">
		@livewire('tournament.scoreboard', ['tournament' => $tournament])
		<flux:separator vertical variant="subtle" />
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-5">
				Games
				@if ($tournament->games->count() > 0)
					@livewire('tournament.game-simulation-status', ['tournament' => $tournament])
				@endif
			</flux:heading>

			<div class="flex flex-col gap-3">
				@forelse ($tournament->games as $game)
					@livewire('tournament.game-item', ['game' => $game], key('game-' . $game->id))
				@empty
					@if ($tournament->status === TournamentStatus::OPEN)
						<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
							<flux:icon name="loading" class="text-zinc-400" />
							Tournament hasn't started yet.
						</div>
					@elseif ($tournament->status === TournamentStatus::ONGOING)
						<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
							<flux:icon name="loading" class="text-zinc-400" />
							Generating matches...
						</div>
					@endif
				@endforelse
			</div>
		</div>
	</div>
</x-layouts.app.layout>

@script
	<script>
		Echo.private('tournaments.{{ $tournament->id }}')
			.listen('PlayersGenerated', (e) => {
				$wire.refresh();
			})
			.listen('GamesGenerated', (e) => {
				$wire.refresh();
			})
			.listen('TournamentUpdated', (e) => {
				$wire.refresh();
			});
	</script>
@endscript
