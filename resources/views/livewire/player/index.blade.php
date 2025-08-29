@php
    use App\Enums\TournamentStatus;
@endphp

<x-layouts.app.layout :heading="__('Players')" :subheading="__('Manage your players')">
	<div class="lg:p-8 flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
		<div class="grid grid-cols-8 gap-4 items-end">
			{{-- <div class="col-start-1 col-end-3">
				@livewire('player.form')
			</div> --}}
			<div class="col-span-2 col-end-7 justify-end">
			</div>
			<div class="col-span-3 col-end-10">
				<flux:input type="search" placeholder="Search Players" wire:model.live.debounce.500ms="search" />
			</div>
		</div>
		<x-flux.table.table :paginate="$this->players">
			<x-flux.table.columns>
				<x-slot name="columns">
					<x-flux.table.column sortable :sorted="$sortBy === 'name'" :dir="$sortDirection" name="Name" wire:click="sort('name')" />
					<x-flux.table.column name="Wins" />
					<x-flux.table.column name="Losses" />
					<x-flux.table.column name="Skill" />
					<x-flux.table.column sortable :sorted="$sortBy === 'created_at'" :dir="$sortDirection" name="Creation date" wire:click="sort('created_at')"/>
					<x-flux.table.column />
				</x-slot>
			</x-flux.table.columns>
			<x-flux.table.rows>
				@forelse ($this->players as $player)
					<x-flux.table.row wire:key="{{ $player->id }}">
						<x-flux.table.cell>{{ $player->name }}</x-flux.table.cell>
						<x-flux.table.cell>---</x-flux.table.cell>
						<x-flux.table.cell>---</x-flux.table.cell>
						<x-flux.table.cell>{{ $player->skill }}</x-flux.table.cell>
						<x-flux.table.cell>{{ $player->created_at->format('d-m-Y H:i') }}</x-flux.table.cell>
						<x-flux.table.cell>
							<div class="flex justify-end">
								<flux:button icon="eye" variant="ghost" href="{{ route('player.show', $player) }}">View</flux:button>
							</div>
						</x-flux.table.cell>
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
			</x-flux.table.rows>
		</x-flux.table.table>
	</div>
</x-layouts.app.layout>