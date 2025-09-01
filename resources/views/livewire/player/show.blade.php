@php
    use App\Enums\TournamentStatus;
@endphp

<x-layouts.app.layout>
	<x-slot name="heading">
		<div class="flex items-center gap-3">
			@if (!empty($player->avatar()))
				<flux:avatar size="lg" circle src="{{ $player->avatar() }}" />
			@else
				<flux:avatar size="lg" circle icon="user" />
			@endif
			{{ $player->name }}
		</div>
	</x-slot>
	<x-slot name="subheading">
		<div class="flex justify-between items-end">
			<div class="flex items-center gap-2">
				<div class="flex flex-col">
					<span>Creation date</span>
					<flux:badge icon="calendar-days">{{ $player->created_at->format('d-m-Y H:i') }}</flux:badge>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Tournament wins</span>
					<flux:button size="xs" icon="trophy" variant="primary" color="amber" class="h-7">{{ $player->tournaments->filter(fn($t) => $t->winner_id === $player->id)->count() }}</flux:button>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Tournaments</span>
					<flux:badge icon="list-bullet">{{ $player->tournaments->count() }}</flux:badge>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Game wins</span>
					<flux:button size="xs" icon="trophy" variant="primary" color="teal" class="h-7">{{ $player->games->filter(fn($g) => $g->winner_id === $player->id)->count() }}</flux:button>
				</div>
				<flux:separator vertical variant="subtle" />
				<div class="flex flex-col">
					<span>Games</span>
					<flux:badge icon="list-bullet">{{ $player->games->count() }}</flux:badge>
				</div>
			</div>
		</div>
	</x-slot>

	<div class="flex items-start max-md:flex-col gap-6 w-full">
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-5">
				Tournament history
			</flux:heading>

			<div class="flex flex-col gap-3">
				@forelse ($this->tournaments as $tournament)
					<flux:button variant="filled" href="{{ route('tournament.show', $tournament) }}" class="flex justify-between items-center p-3 w-full h-full">
						<div class="flex items-center gap-2 w-full">
							<flux:heading size="xl" level="2" class="
								{{ match ($tournament->getPlayerPosition($player)) {
									1 => 'text-amber-500 font-bold',
									2 => 'text-gray-400 font-bold',
									3 => 'text-orange-700 font-bold',
									default => 'text-zinc-500',
								} }}
							">
								#{{ $tournament->getPlayerPosition($player) }}
							</flux:heading>
							<flux:separator vertical variant="subtle"/>
							{{ $tournament->name }}
						</div>
						<div class="flex flex-col gap-2">
							<span class="">{{ $tournament->created_at->format('d-m-Y H:i') }}</span>
						</div>
						<flux:separator vertical variant="subtle"/>
						<div class="flex flex-col gap-2">
							<flux:badge icon="{{ match ($tournament->status) {
								TournamentStatus::OPEN => 'check-circle',
								TournamentStatus::ONGOING => 'loading',
								TournamentStatus::ENDED => 'x-circle',
							} }}" class="" size="sm" color="{{ match ($tournament->status) {
								TournamentStatus::OPEN => 'teal',
								TournamentStatus::ONGOING => 'orange',
								TournamentStatus::ENDED => 'zinc',
							} }}">{{ Str::title($tournament->status->value) }}</flux:badge>
							Players: {{ $tournament->players->count() }}
						</div>
					</flux:button>
				@empty
					<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
						Player has not participated in any tournaments.
					</div>
				@endforelse

				{{ $this->tournaments->links('vendor.livewire.tailwind') }}
			</div>
		</div>
		<flux:separator vertical variant="subtle" />
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-5">
				Game history
			</flux:heading>

			<div class="flex flex-col gap-3">
				@forelse ($player->games as $game)
				@empty
					<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
						No games to display.
					</div>
				@endforelse
			</div>
		</div>
	</div>
</x-layouts.app.layout>