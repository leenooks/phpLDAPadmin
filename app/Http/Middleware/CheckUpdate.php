<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckUpdate
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		return $next($request);
	}

	/**
	 * Handle tasks after the response has been sent to the browser.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response  $response
	 * @return void
	 */
	public function terminate($request, $response)
	{
		Cache::remember('version',60*5,function() {
			// CURL call to URL to see if there is a new version
			Log::debug(sprintf('Checking for updates for [%s]',config('app.version')));

			return TRUE;
		});
	}
}