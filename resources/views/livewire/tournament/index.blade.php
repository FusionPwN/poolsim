@php
    use App\Enums\TournamentStatus;
@endphp

<x-layouts.app.layout :heading="__('Tournaments')" :subheading="__('Manage your tournaments')">
	<div class="lg:p-8 flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
		<div class="grid grid-cols-8 gap-4 items-end">
			<div class="col-start-1 col-end-3">
				@livewire('tournament.form')
			</div>
			<div class="col-span-2 col-end-7 justify-end">
				<flux:button.group class="justify-end">
					<flux:button
						icon="{{ $this->status === '' ? 'check-circle' : null }}"
						variant="filled"
						wire:click="filterByStatus('')"
					>
						All
					</flux:button>
					@foreach (TournamentStatus::cases() as $status)
						<flux:button
							icon="{{ $this->status === $status->value ? 'check-circle' : null }}"
							variant="filled"
							wire:click="filterByStatus('{{ $status->value }}')"
						>
							{{ Str::title($status->value) }}
						</flux:button>
					@endforeach
				</flux:button.group>
			</div>
			<div class="col-span-3 col-end-10">
				<flux:input type="search" placeholder="Search Tournaments" wire:model.live.debounce.500ms="search" />
			</div>
		</div>
		<x-flux.table.table :paginate="$this->tournaments">
			<x-flux.table.columns>
				<x-slot name="columns">
					<x-flux.table.column sortable :sorted="$sortBy === 'name'" :dir="$sortDirection" name="Name" wire:click="sort('name')" />
					<x-flux.table.column name="Status" />
					<x-flux.table.column sortable :sorted="$sortBy === 'created_at'" :dir="$sortDirection" name="Creation date" wire:click="sort('created_at')"/>
					<x-flux.table.column />
				</x-slot>
			</x-flux.table.columns>
			<x-flux.table.rows>
				@forelse ($this->tournaments as $tournament)
					<x-flux.table.row wire:key="{{ $tournament->id }}">
						<x-flux.table.cell>{{ $tournament->name }}</x-flux.table.cell>
						<x-flux.table.cell>
							<flux:badge size="sm" color="{{ match ($tournament->status) {
								TournamentStatus::OPEN => 'teal',
								TournamentStatus::ONGOING => 'orange',
								TournamentStatus::ENDED => 'zinc',
							} }}">{{ Str::title($tournament->status->value) }}</flux:badge>
						</x-flux.table.cell>
						<x-flux.table.cell>
							{{ $tournament->created_at->format('d-m-Y H:i') }}
						</x-flux.table.cell>
						<x-flux.table.cell>
							<div class="flex justify-end">
								<flux:button icon="eye" variant="ghost" href="{{ route('tournament.show', $tournament) }}">View</flux:button>
							</div>
						</x-flux.table.cell>
					</x-flux.table.row>
				@empty
					<x-flux.table.row>
						<x-flux.table.cell colspan="4">
							<div class="py-4 text-center text-sm text-zinc-500">
								No tournaments found.
							</div>
						</x-flux.table.cell>
					</x-flux.table.row>
				@endforelse
			</x-flux.table.rows>
		</x-flux.table.table>
	</div>
</x-layouts.app.layout>

@script
	<script>
		Echo.private('tournaments')
			.listen('TournamentUpdated', (e) => {
				$wire.refresh();
			});
	</script>
@endscript