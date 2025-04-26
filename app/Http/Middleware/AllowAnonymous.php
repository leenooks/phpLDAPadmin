<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AllowAnonymous
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request,Closure $next): mixed
	{
		if ((! config('pla.allow_guest',FALSE))
			&& ($request->path() !== 'login')
			&& ((! Cookie::has('username_encrypt')) || (! Cookie::has('password_encrypt'))))
			return redirect()
				->to('/login');

		return $next($request);
	}
}