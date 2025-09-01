@php
    use App\Enums\TournamentStatus;
    use App\Enums\GameStatus;
@endphp

<x-layouts.app.layout>
	<x-slot name="heading">
        <div class="flex flex-col gap-3">
            <div>
                <div class="flex items-center gap-3">
                    Game #{{ $game->sequence }}
                    <flux:badge icon="{{ match ($game->status) {
                        GameStatus::SCHEDULED => 'check-circle',
                        GameStatus::ONGOING => 'loading',
                        GameStatus::ENDED => 'x-circle',
                    } }}" class="" size="sm" color="{{ match ($game->status) {
                        GameStatus::SCHEDULED => 'teal',
                        GameStatus::ONGOING => 'orange',
                        GameStatus::ENDED => 'zinc',
                    } }}">{{ Str::title($game->status->value) }}</flux:badge>
                </div>
                <div class="flex items-center gap-3">
                    <flux:icon name="trophy"/>
                    {{ $game->tournament->name }} 
                    <flux:badge icon="{{ match ($game->tournament->status) {
                        TournamentStatus::OPEN => 'check-circle',
                        TournamentStatus::ONGOING => 'loading',
                        TournamentStatus::ENDED => 'x-circle',
                    } }}" class="" size="sm" color="{{ match ($game->tournament->status) {
                        TournamentStatus::OPEN => 'teal',
                        TournamentStatus::ONGOING => 'orange',
                        TournamentStatus::ENDED => 'zinc',
                    } }}">{{ Str::title($game->tournament->status->value) }}</flux:badge>
                </div>
            </div>
        </div>
	</x-slot>
    <x-slot name="subheading">
		<div class="flex justify-between items-end">
			<div class="flex items-center gap-2">
                @if ($game->isEnded())
                    <div class="flex flex-col">
                        <span>Winner</span>
                        <flux:button href="{{ route('player.show', $game->winner) }}" icon="hand-thumb-up" variant="primary" color="amber" size="xs" class="h-7">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->winner->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->winner->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->winner->name }}
                            </div>
                        </flux:button>
                    </div>
                    <flux:separator vertical variant="subtle" />
                    <div class="flex flex-col">
                        <span>Loser</span>
                        <flux:button href="{{ route('player.show', $game->loser) }}" icon="hand-thumb-down" variant="primary" color="red" size="xs" class="h-7">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->loser->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->loser->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->loser->name }}
                            </div>
                        </flux:button>
                    </div>
                    <flux:separator vertical variant="subtle" />
                    <div class="flex flex-col">
                        <span>Total Fouls</span>
                        <flux:button icon="x-mark" size="xs" variant="primary" class="h-7" color="orange">{{ $game->total_fouls }}</flux:button>
                    </div>
                @else
                    <div class="flex flex-col">
                        <span>Player 1</span>
                        <flux:button href="{{ route('player.show', $game->player1) }}" icon="user" color="teal" size="xs" class="h-7">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->player1->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->player1->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->player1->name }}
                            </div>
                        </flux:button>
                    </div>
                    <flux:separator vertical variant="subtle" />
                    <div class="flex flex-col">
                        <span>Player 2</span>
                        <flux:button href="{{ route('player.show', $game->player2) }}" icon="user" color="teal" size="xs" class="h-7">
                            <div class="flex items-center gap-2">
                                @if (!empty($game->player2->avatar()))
                                    <flux:avatar size="xs" circle src="{{ $game->player2->avatar() }}" />
                                @else
                                    <flux:avatar size="xs" circle icon="user" />
                                @endif
                                {{ $game->player2->name }}
                            </div>
                        </flux:button>
                    </div>
                @endif
			</div>
		</div>
	</x-slot>

    <div class="flex flex-col gap-3 w-full">
        @if ($game->isEnded())
            <div class="grid grid-cols-5 gap-4 w-full">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <flux:badge icon="user" color="teal">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->winner->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->winner->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->winner->name }}
                        </div>
                    </flux:badge>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    vs
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <flux:badge icon="user" color="teal">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->loser->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->loser->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->loser->name }}
                        </div>
                    </flux:badge>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-4 w-full mt-3">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <span>{{ $game->winner->skill }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Skill
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->loser->skill }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-3">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <span>{{ $game->getFoulsWinner() }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Fouls
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getFoulsLoser() }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-2">
                <div class="col-start-0 col-span-2">
                    <span>0</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Balls left
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getLosingBallsLeft() }}</span>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <div class="grid grid-cols-5 gap-4 w-full mt-2">
                <div class="col-start-0 col-span-2">
                    <span>{{ $game->getWinningBallType() }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Ball type
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->getLosingBallType() }}</span>
                </div>
            </div>
        @else
            <div class="grid grid-cols-5 gap-4 w-full">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <flux:badge icon="user" color="teal">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->player1->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->player1->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->player1->name }}
                        </div>
                    </flux:badge>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    vs
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <flux:badge icon="user" color="teal">
                        <div class="flex items-center gap-2">
                            @if (!empty($game->player2->avatar()))
                                <flux:avatar size="xs" circle src="{{ $game->player2->avatar() }}" />
                            @else
                                <flux:avatar size="xs" circle icon="user" />
                            @endif
                            {{ $game->player2->name }}
                        </div>
                    </flux:badge>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-4 w-full mt-3">
                <div class="col-start-0 col-span-2 flex justify-start">
                    <span>{{ $game->player1->skill }}</span>
                </div>
                <div class="flex items-center justify-center text-zinc-500">
                    Skill
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                    <span>{{ $game->player2->skill }}</span>
                </div>
            </div>
        @endif

        

        <flux:separator variant="subtle" />

        <div class="w-full mt-4">
            <div class="grid grid-cols-5 gap-4 w-full mt-2">
                <div class="col-start-0 col-span-2">
                </div>
                <div class="flex items-center justify-center">
                     <flux:heading size="lg" level="1" class="mb-5">Action history</flux:heading>
                </div>
                <div class="col-start-4 col-span-2 flex justify-end">
                </div>
            </div>

            @php $index = 0; @endphp
            @forelse ($turns as $turn)
                <div class="grid grid-cols-5 gap-4 w-full mt-2">
                    <div class="col-start-0 col-span-2 flex-col w-full">
                        @if ($game->winner_id === $turn['player_id'])
                            @foreach ($turn['actions'] as $action)
                                @foreach ($action['descriptions'] as $description)
                                    <span class="flex text-sm font-semibold">
                                        {{ $description }}
                                    </span>
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                    <div class="flex items-center justify-center gap-3 text-sm text-zinc-500">
                        <span class="animate-ping rounded-full bg-sky-500 @if ($game->winner_id === $turn['player_id']) opacity-75 @else opacity-0 @endif" style="width: 5px; height: 5px;"></span>
                        Turn #{{ ++$index }}
                        <span class="animate-ping rounded-full bg-sky-500 @if ($game->loser_id === $turn['player_id']) opacity-75 @else opacity-0 @endif" style="width: 5px; height: 5px;"></span>
                    </div>
                    <div class="col-start-4 col-span-2 flex justify-end flex-col w-full">
                        @if ($game->loser_id === $turn['player_id'])
                            @foreach ($turn['actions'] as $action)
                                @foreach ($action['descriptions'] as $description)
                                    <span class="flex justify-end text-sm font-semibold">
                                        {{ $description }}
                                    </span>
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                </div>
            @empty
                 <div class="flex gap-4 items-center justify-center py-4 text-center text-sm text-zinc-500">
                    No actions to show.
                </div>
            @endforelse
        </div>
</x-layouts.app.layout>