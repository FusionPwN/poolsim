<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AvatarApi;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

beforeEach(function () {
	config(['avatar.storage_path' => class_basename(__FILE__) . '/avatars/']);
});
afterEach(function () {
	Storage::disk('public')->deleteDirectory(class_basename(__FILE__));
});

it('generates avatar and stores images', function () {
    $avatar = app(AvatarApi::class);
    $result = $avatar->generate('male', 'TestUser');

    expect($result)->toHaveKey('original');
    expect($result['original'])->not->toBeNull();
    expect($result['original']['url'])->toContain(config('avatar.storage_path') . 'originals/');
    Storage::disk('public')->assertExists($result['original']['path']);

    foreach (config('avatar.sizes', []) as $size) {
        expect($result)->toHaveKey($size);
        expect($result[$size])->not->toBeNull();
        expect($result[$size]['url'])->toContain(config('avatar.storage_path') . "$size/");
        Storage::disk('public')->assertExists($result[$size]['path']);
    }
});
