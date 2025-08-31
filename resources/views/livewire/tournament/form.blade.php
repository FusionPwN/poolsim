<div>
    <flux:modal.trigger name="new-tournament">
		<flux:button icon="plus" variant="filled">Create tournament</flux:button>
	</flux:modal.trigger>

	<flux:modal :closable="false" name="new-tournament" variant="flyout">
		<div class="absolute top-0 end-0 mt-4 me-4">
			<flux:modal.close>
				<flux:button x-on:click="$flux.modal('new-tournament').close()" variant="ghost" icon="x-mark" size="sm" alt="Close modal" class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!"></flux:button>
			</flux:modal.close>
		</div>
		<div class="space-y-6">
			<div>
				<flux:heading size="lg">Create Tournament</flux:heading>
				<flux:text class="mt-2">Fill in the details to create a new tournament.</flux:text>
			</div>
			<form wire:submit.prevent="createTournament" class="space-y-6">
				<flux:input type="text" label="Name" placeholder="Tournament name" wire:model.defer="name" />
				<flux:input type="number" label="Number of players" placeholder="Number of players" min="1" step="1" wire:model.defer="players" x-on:input="if ($el.value < 1) $el.value = 1; $el.value = $el.value.replace(/\D/, '')" />
				<flux:radio.group wire:model.defer="simulation" label="Simulation" variant="segmented">
					<flux:radio label="Automatic" value="automatic"/>
					<flux:radio label="Manual" value="manual" />
				</flux:radio.group>
				<div class="flex">
					<flux:spacer />
					<flux:button type="submit" variant="primary">Save changes</flux:button>
				</div>
			</form>
		</div>
	</flux:modal>
</div>
