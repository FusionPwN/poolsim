<section class="w-full">
	@include('partials.settings-heading')

	<x-settings.layout :heading="__('API')" :subheading="__('Update your API token')">
		<form wire:submit="generateApiToken" class="my-6 w-full space-y-6">
			<flux:input wire:model="apiToken" :label="__('API Token')" type="text" readonly />
			<div class="flex items-center gap-4">
				<div class="flex items-center justify-end">
					<flux:button variant="primary" type="submit" class="w-full">{{ __('Generate Token') }}</flux:button>
				</div>
			</div>
		</form>
	</x-settings.layout>
</section>
