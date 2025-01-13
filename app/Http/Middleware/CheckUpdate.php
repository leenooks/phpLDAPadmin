<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckUpdate
{
	private const UPDATE_SERVER = 'https://version.phpldapadmin.org';
	private const UPDATE_TIME = 60*60*6;

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		Config::set('update_available',Cache::get('upstream_version'));

		return $next($request);
	}

	/**
	 * Handle tasks after the response has been sent to the browser.
	 *
	 * @return void
	 */
	public function terminate(): void
	{
		Cache::remember('upstream_version',self::UPDATE_TIME,function() {
			// CURL call to URL to see if there is a new version
			Log::debug(sprintf('CU_:Checking for updates for [%s]',config('app.version')));

			$client = new Client;

			try {
				$response = $client->request('POST',sprintf('%s/%s',self::UPDATE_SERVER,strtolower(config('app.version'))));

				if ($response->getStatusCode() === 200) {
					$result = json_decode($response->getBody());

					Log::debug(sprintf('CU_:- Update server returned...'),['update'=>$result]);

					return $result;
				}

			} catch (\Exception $e) {
				Log::debug(sprintf('CU_:- Exception connecting to update server'),['e'=>get_class($e)]);
			}

			return NULL;
		});
	}
}