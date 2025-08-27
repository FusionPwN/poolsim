<?php

return [
	'url' => env('AVATAR_API_URL', 'https://avatar.iran.liara.run/public'),

	'storage_path' => env('AVATAR_STORAGE_PATH', 'avatars/'),
	'sizes' => [ '640_360' ],
	'quality' => 100,
];