@php
    use App\Enums\GameStatus;
@endphp
<div class="flex items-center gap-2">
    @if ($tournament->games->where('status', GameStatus::ONGOING)->count() > 0)
        <flux:badge icon="loading" size="sm" color="teal">Simulating ({{ $tournament->games->where('status', GameStatus::ONGOING)->count() }}/{{ $tournament->games->count() }})</flux:badge>
    @else
	    <flux:badge size="sm" color="green">Simulated ({{ $tournament->games->where('status', GameStatus::ENDED)->count() }}/{{ $tournament->games->count() }})</flux:badge>
    @endif
</div>

@script
    <script>
        Echo.private('tournaments.{{ $tournament->id }}')
            .listen('GamesGenerated', (e) => {
                $wire.refresh();
            })
            .listen('GameStarted', (e) => {
				$wire.refresh();
			})
            .listen('GameFinished', (e) => {
				$wire.refresh();
			});
    </script>
@endscript