<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use App\Traits\MenuHistory;

class DummyHistoryClass {
    use MenuHistory;
}

it('stores only unique and latest items up to the limit, newest first', function () {
    Session::flush();
    $dummy = new DummyHistoryClass();
    $dummy->limit = 3;

    // Add 4 items, some with duplicate IDs
    $dummy->addToHistory('menu', 'ModelA', 1, 'First');
    $dummy->addToHistory('menu', 'ModelB', 2, 'Second');
    $dummy->addToHistory('menu', 'ModelC', 3, 'Third');
    $dummy->addToHistory('menu', 'ModelA', 1, 'First Updated');

    $history = Session::get('menu_history')['menu'];

    // Should only keep 3 items, newest first, and only one with id=1
    expect($history)->toHaveCount(3);
    expect($history[0]['id'])->toBe(1);
    expect($history[0]['name'])->toBe('First Updated');
    expect($history[1]['id'])->toBe(3);
    expect($history[2]['id'])->toBe(2);
});
