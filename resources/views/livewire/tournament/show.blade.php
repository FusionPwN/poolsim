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
		<div class="flex items-center gap-2">
			<div class="flex flex-col">
				<span>Creation date</span>
				<flux:badge icon="calendar-days">{{ $tournament->created_at->format('d-m-Y H:i') }}</flux:badge>
			</div>
			<flux:separator vertical variant="subtle" />
			<div class="flex flex-col">
				<span>Players</span>
				<flux:badge icon="user-group">0000000000000000000</flux:badge>
			</div>
		</div>
	</x-slot>

	

	<div class="flex items-start max-md:flex-col gap-6 w-full">
		<div class="w-full">
			<flux:heading size="lg" level="1" class="mb-3">Scoreboard</flux:heading>
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
							<x-flux.table.cell>{{ $player->name }}</x-flux.table.cell>
							<x-flux.table.cell class="text-center text-teal-500! dark:text-teal-400!">---</x-flux.table.cell>
							<x-flux.table.cell class="text-center text-red-500! dark:text-red-400!">---</x-flux.table.cell>
							<x-flux.table.cell class="text-center">---</x-flux.table.cell>
							<x-flux.table.cell class="text-center font-black">{{ $player->pivot->points }}</x-flux.table.cell>
						</x-flux.table.row>
					@empty
						<x-flux.table.row>
							<x-flux.table.cell colspan="6">
								<div class="py-4 text-center text-sm text-zinc-500">
									No players found.
								</div>
							</x-flux.table.cell>
						</x-flux.table.row>
					@endforelse
				</x-flux.table.row>
			</x-flux.table.table>
		</div>
		<flux:separator vertical variant="subtle" />
		<div class="w-full">
			<flux:heading size="lg" level="1" class="flex items-center gap-3 mb-3">
				Games
				<flux:badge icon="loading" size="sm" color="teal">Simulating (x/x)</flux:badge>
				<flux:badge size="sm" color="green">Simulated (x/x)</flux:badge>
			</flux:heading>
		</div>
	</div>

	

	
</x-layouts.app.layout>
