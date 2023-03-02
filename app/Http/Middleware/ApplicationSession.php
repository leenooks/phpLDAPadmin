<?php

namespace App\Http\Middleware;

use App\Classes\LDAP\Server;
use App\Ldap\User;
use Closure;

/**
 * This sets up our application session with any required values, ultimately for cache optimisation reasons
 */
class ApplicationSession
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request,Closure $next)
	{
		view()->share('user', auth()->user() ?: new User);

		\Config::set('server',new Server);

		return $next($request);
	}
}