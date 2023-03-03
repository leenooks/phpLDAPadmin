<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Session;
use LdapRecord\Container;

use App\Ldap\Connection;

class SwapinAuthUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
		$key = config('ldap.default');

		/*
		// Rebuild our connection with the authenticated user.
		if (Session::has('username_encrypt') && Session::has('password_encrypt')) {
			Config::set('ldap.connections.'.$key.'.username',Crypt::decryptString(Session::get('username_encrypt')));
			Config::set('ldap.connections.'.$key.'.password',Crypt::decryptString(Session::get('password_encrypt')));

		} else
		*/

		if (Cookie::has('username_encrypt') && Cookie::has('password_encrypt')) {
			Config::set('ldap.connections.'.$key.'.username',Cookie::get('username_encrypt'));
			Config::set('ldap.connections.'.$key.'.password',Cookie::get('password_encrypt'));

			Log::debug('Swapping out configured LDAP credentials with the user\'s cookie.');
		}

		// We need to override our Connection object so that we can store and retrieve the logged in user and swap out the credentials to use them.
		Container::getInstance()->addConnection(new Connection(config('ldap.connections.'.$key)),$key);

		return $next($request);
    }
}