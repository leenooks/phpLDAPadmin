<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Classes\LDAP\Server;

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

		return $next($request);
	}
}