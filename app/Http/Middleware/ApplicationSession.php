<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use Illuminate\Http\Request;

use App\Classes\LDAP\Server;
use App\Ldap\User;

/**
 * This sets up our application session with any required values, ultimately for cache optimisation reasons
 */
class ApplicationSession
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request,Closure $next): mixed
	{
		Config::set('server',new Server);

		view()->share('user', auth()->user() ?: new User);

		return $next($request);
	}
}