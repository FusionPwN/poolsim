@php
	use App\Enums\TournamentStatus;
@endphp

<flux:navlist.item :href="route('tournament.show', $tournament)" :current="request()->routeIs('tournament.show') && request()->route()->tournament->id === $tournament->id" wire:key="tournament-{{ $tournament->id }}" wire:navigate>
    <div class="flex justify-between items-center">
        {{ Str::limit($tournament->name, 10) }}

        <flux:badge icon="{{ match ($tournament->status) {
            TournamentStatus::OPEN => 'check-circle',
            TournamentStatus::ONGOING => 'loading',
            TournamentStatus::ENDED => 'x-circle',
        } }}" class="" size="sm" color="{{ match ($tournament->status) {
            TournamentStatus::OPEN => 'teal',
            TournamentStatus::ONGOING => 'orange',
            TournamentStatus::ENDED => 'zinc',
        } }}">{{ Str::title($tournament->status->value) }}</flux:badge>
    </div>
</flux:navlist.item>

@script
    <script>
        Echo.private('tournaments.{{ $tournament->id }}')
            .listen('TournamentUpdated', (e) => {
                $wire.refresh();
            });
    </script>
@endscript