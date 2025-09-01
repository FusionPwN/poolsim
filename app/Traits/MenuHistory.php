<?php

namespace App\Traits;

trait MenuHistory
{
	# Could be a middleware, since it's a demo this is easier

    public int $limit = 5;

    /**
     * Add an item to the menu history, keeping only unique and latest items up to the limit.
     *
     * @param string $menuItem
     * @param string $model
     * @param int $id
     * @param string $name
     * @return void
     */
    public function addToHistory(string $menuItem, string $model, int $id, string $name): void
    {
        $history = session()->get('menu_history', []);
        $newItem = [
            'model' => $model,
            'id' => $id,
            'name' => $name,
        ];

        // prepend
        $items = $history[$menuItem] ?? [];
        array_unshift($items, $newItem);

        // check for duplicates
        $unique = [];
        foreach ($items as $item) {
            if (!isset($unique[$item['id']])) {
                $unique[$item['id']] = $item;
            }
        }

        // limit to the latest $limit items
        $history[$menuItem] = array_slice(array_values($unique), 0, $this->limit);

        session()->put('menu_history', $history);
    }
    /**
     * Get the current menu history from session.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getMenuHistory(): array
    {
        return session()->get('menu_history', []);
    }
}
