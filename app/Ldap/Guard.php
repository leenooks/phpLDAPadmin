<?php

namespace App\Ldap;

use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\Crypt;
use LdapRecord\Auth\Guard as GuardBase;

class Guard extends GuardBase
{
	public function attempt(string $username, string $password, bool $stayBound = false): bool
	{
		if ($result = parent::attempt($username,$password,$stayBound)) {
			/*
			 * We can either use our session or cookies to store this. If using session, then Http/Kernel needs to be
			 * updated to start a session for API calls.
			// We need to store our password so that we can swap in the user in during SwapinAuthUser::class middleware
			request()->session()->put('username_encrypt',Crypt::encryptString($username));
			request()->session()->put('password_encrypt',Crypt::encryptString($password));
			*/

			// For our API calls, we store the cookie - which our cookies are already encrypted
			Cookie::queue('username_encrypt',$username);
			Cookie::queue('password_encrypt',$password);
		}

		return $result;
	}
}