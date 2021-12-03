<?php

namespace App\Http\Middleware;

use App\Ldap\User;
use Closure;

/**
 * Class GuestUser
 * @package Leenooks\Laravel\Http\Middleware
 */
class GuestUser
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

        return $next($request);
    }
}