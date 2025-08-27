<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class AvatarApi
{
	public function __construct() 
	{
	}

	/**
	 * @return array<string, null|string[]>
	 */
	public function generate(?string $gender = null, ?string $name = null): array
	{
		$url = config('avatar.url');

		if ($gender === 'male') {
			$url .= '/boy';
		} elseif ($gender === 'female') {
			$url .= '/girl';
		}

		$params = $name ? ['username' => $name] : [];
		$response = Http::get($url, $params);

		if (!$response->successful()) {
			throw new \Exception("Avatar API request failed");
		}

		$result = []; 

		$manager = new ImageManager(Driver::class);
		$image = $response->body();
		$instance = $manager->read($image);

		$filename = uniqid('avatar_') . '.webp';

		// Always process originals first and save to webp format
		$path = config('avatar.storage_path') . "originals/$filename";
		Storage::disk('local')->put($path, $instance->toWebp(config('avatar.quality')));
		$result['original'] = [
			'path' => $path,
			'url' => Storage::disk('local')->url($path)
		];

		foreach (config('avatar.sizes', []) as $size) {
			if (preg_match('/^\\d+_\\d+$/', $size)) {
				[$width, $height] = explode('_', $size);
				$width = (int) $width;
				$height = (int) $height;
				$temp = $instance->scale($width, $height);

				$path = config('avatar.storage_path') . "$size/$filename";
				Storage::disk('local')->put($path, $temp->toWebp(config('avatar.quality')));
				
				$result[$size] = [
					'path' => $path,
					'url' => Storage::disk('local')->url($path)
				];
			}
		}

		return $result;
	}
}