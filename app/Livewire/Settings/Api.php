<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Api extends Component
{
	public string $apiToken = '';
	
	public function generateApiToken(): void
	{
		$user = Auth::user();
		$user->tokens()->where('name', 'api-token')->delete();
		$token = $user->createToken('api-token');
		$this->apiToken = $token->plainTextToken;
	}

    public function render(): \Illuminate\View\View
    {
        return view('livewire.settings.api');
    }
}
