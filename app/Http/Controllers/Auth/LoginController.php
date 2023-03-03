<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

	protected function credentials(Request $request): array
	{
		return [
			'mail' => $request->get('email'),
			'password' => $request->get('password'),
		];
	}

	/**
	 * We need to delete our encrypted username/password cookies
	 *
	 * @note The rest of this function is the same as a normal laravel logout as in AuthenticatesUsers::class
	 * @param Request $request
	 * @return \Illuminate\Contracts\Foundation\Application|JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
	 */
	public function logout(Request $request)
	{
		// Delete our LDAP authentication cookies
		Cookie::queue(Cookie::forget('username_encrypt'));
		Cookie::queue(Cookie::forget('password_encrypt'));

		$this->guard()->logout();

		$request->session()->invalidate();

		$request->session()->regenerateToken();

		if ($response = $this->loggedOut($request)) {
			return $response;
		}

		return $request->wantsJson()
			? new JsonResponse([], 204)
			: redirect('/');
	}

	/**
	 *
	 * Show our themed login page
	 */
	public function showLoginForm()
	{
		$login_note = '';

		if (file_exists('login_note.txt'))
			$login_note = file_get_contents('login_note.txt');

		return view('architect::auth.login')->with('login_note',$login_note);
	}
}
