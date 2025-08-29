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
					<flux:badge icon="user-group">{{ $this->players->count() }}</flux:badge>
				</div>
				@if ($winner)
					<flux:separator vertical variant="subtle" />
					<div class="flex flex-col">
						<span>Winner</span>
						<flux:button href="#" size="xs" icon="trophy" variant="primary" color="amber" class="h-7">{{ $winner->name }}</flux:button>
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
		<div class="w-full sticky top-[1rem]">
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
							<x-flux.table.cell class="text-center font-black">{{ $loop->index + 1 }}</x-flux.table.cell>
							<x-flux.table.cell>
								<flux:button href="#" icon="user" size="xs" variant="ghost">{{ $player->name }}</flux:button>
							</x-flux.table.cell>
							<x-flux.table.cell class="text-center text-teal-500! dark:text-teal-400!">---</x-flux.table.cell>
							<x-flux.table.cell class="text-center text-red-500! dark:text-red-400!">---</x-flux.table.cell>
							<x-flux.table.cell class="text-center">---</x-flux.table.cell>
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
		<flux:separator vertical variant="subtle" />
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-5">
				Games
				@if ($tournament->games->count() > 0 && $tournament->status === TournamentStatus::ONGOING)
					<flux:badge icon="loading" size="sm" color="teal">Simulating (x/{{ $tournament->games->count() }})</flux:badge>
					<flux:badge size="sm" color="green">Simulated (x/{{ $tournament->games->count() }})</flux:badge>
				@endif
			</flux:heading>

			@if ($tournament->games->count() === 0 && $tournament->status === TournamentStatus::OPEN)
				<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
					<flux:icon name="loading" class="text-zinc-400" />
					Tournament hasn't started yet.
				</div>
			@elseif ($tournament->games->count() === 0 && $tournament->status === TournamentStatus::ONGOING)
				<div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
					<flux:icon name="loading" class="text-zinc-400" />
					Generating matches...
				</div>
			@endif

			{{-- <div class="flex flex-col gap-3">
				@for ($i = 0; $i < 50; $i++)
					<flux:callout inline>
						<flux:callout.heading>
							<div class="flex items-center justify-between w-full">
								<div class="flex items-center gap-3">
									<flux:badge icon="hand-thumb-up" color="amber">Player X</flux:badge>
									vs
									<flux:badge icon="hand-thumb-down" color="red">Player Y</flux:badge>
								</div>
							</div>
						</flux:callout.heading>

						<flux:callout.text>
							<div class="flex items-center gap-2">
								<span>Player X - Fouls: 3</span>
								<flux:separator vertical variant="subtle" />
								<span>Player Y - Fouls: 3</span>
								<flux:separator vertical variant="subtle" />
								<span>Player Y - Balls left: 3</span>
							</div>
						</flux:callout.text>

						<x-slot name="actions" class="h-full">
							<flux:button :loading="false" icon="loading" disabled>
							</flux:button>
							<flux:button :loading="false" icon="play-circle">
								Simulate
							</flux:button>
							<flux:button :loading="false" icon="document-magnifying-glass" class="cursor-pointer">
								Match details
							</flux:button>
						</x-slot>
					</flux:callout>
				@endfor
			</div> --}}
		</div>
	</div>
</x-layouts.app.layout>
