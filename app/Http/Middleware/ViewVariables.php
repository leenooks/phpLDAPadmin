<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Ldap\User;

/**
 * This sets up our application session with any required values, ultimately for cache optimisation reasons
 */
class ViewVariables
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
		view()->share('server',Config::get('server'));
		view()->share('user',auth()->user() ?: new User);

		return $next($request);
	}
}