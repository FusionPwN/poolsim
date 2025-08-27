@props([
    'sortable' => false,
	'sorted' => false,
	'dir' => 'desc',
	'name' => null
])

@php
@endphp

<th class="py-2 px-2 first:ps-0 last:pe-0 text-start text-sm font-medium text-zinc-800 dark:text-white" {{ $attributes }}>
	<div class="flex in-[.group\/center-align]:justify-center in-[.group\/end-align]:justify-end">
		@if ($sortable)
			<button type="button" class="group/sortable flex items-center gap-1 -my-1 -ms-2 -me-2 px-2 py-1  in-[.group\/end-align]:flex-row-reverse in-[.group\/end-align]:-me-2 in-[.group\/end-align]:-ms-8" data-flux-table-sortable="">
				<div>{{ $name }}</div>

				<div class="rounded-sm text-zinc-400 group-hover/sortable:text-zinc-800 dark:group-hover/sortable:text-white @if($sorted && $dir === 'asc') rotate-180 @endif">
					<svg class="shrink-0 [:where(&amp;)]:size-4" data-flux-icon="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
						<path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
					</svg>
				</div>
			</button>
		@else
			{{ $name }}
		@endif
	</div>
</th>