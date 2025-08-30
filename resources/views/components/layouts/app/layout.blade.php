@props([
    'heading' => null,
    'subheading' => null,
])

<div {{ $attributes->merge(['class' => 'w-full']) }} {{ $attributes }}>
	@if ($heading)
		<div class="relative mb-6 w-full">
			<flux:heading size="xl" level="1" class="flex items-center gap-3">{{ $heading }}</flux:heading>
			<flux:subheading size="lg" class="mb-6">{{ $subheading }}</flux:subheading>
			<flux:separator variant="subtle" />
		</div>
	@endif

	{{ $slot }}
</div>
