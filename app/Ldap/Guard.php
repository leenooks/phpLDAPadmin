<?php

namespace App\Ldap;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use LdapRecord\Auth\Guard as GuardBase;

use App\Classes\LDAP\Attribute\Password;

class Guard extends GuardBase
{
	public function attempt(string $username, string $password, bool $stayBound = false): bool
	{
		Log::info(sprintf('ALG:Attempting login for [%s] with password [%s]',$username,($password ? Password::obfuscate : str_repeat('?',16))));

		if ($result = parent::attempt($username,$password,$stayBound)) {
			// Store user details so we can swap in auth details in SwapinAuthUser
			session()->put('username_encrypt',Crypt::encryptString($username));
			session()->put('password_encrypt',Crypt::encryptString($password));
		}

		return $result;
	}
}