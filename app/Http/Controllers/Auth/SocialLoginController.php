<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use Socialite;

class SocialLoginController extends Controller
{
	public function redirectToProvider($provider)
	{
		return Socialite::with($provider)->redirect();
	}

	public function handleProviderCallback($provider)
	{
		$openiduser = Socialite::with($provider)->user();
		$user = Socialite::with($provider)->findOrCreateUser($openiduser);

		Auth::login($user,FALSE);

		/*
		if (! $user->profile_update)
		{
			return redirect()->to(url('settings'));
		}
		*/

		return redirect()->intended();
	}
}