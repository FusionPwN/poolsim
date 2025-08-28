@props([
	'paginate' => null
])

<div>
	<div class="overflow-auto">
		<table class="[:where(&)]:min-w-full table-auto text-zinc-800 divide-y divide-zinc-800/10 dark:divide-white/20 text-zinc-800 whitespace-nowrap [&_dialog]:whitespace-normal [&_[popover]]:whitespace-normal" data-flux-table>
			<thead class="shadow-sm">
				{{ $slot }}
			</thead>

			<x-flux.table.rows/>
		</table>
	</div>

	@if ($paginate)
		{{ $paginate->links('vendor.livewire.tailwind') }}
	@endif
</div>