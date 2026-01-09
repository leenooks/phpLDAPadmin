<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LdapRecord\Auth\BindException;
use LdapRecord\Container;

use App\Http\Controllers\Controller;
use App\Ldap\Entry;

class LoginController extends Controller
{
	private const LOGKEY = 'CAl';

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
	protected $redirectTo = '/';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest')
			->except('logout');
	}

	protected function credentials(Request $request): array
	{
		return [
			login_attr_name() => $request->get(login_attr_name()),
			'password' => $request->get('password'),
		];
	}

	/**
	 * When attempt to login
	 *
	 * @param Request $request
	 * @return bool
	 * @throws \LdapRecord\ConnectionException
	 * @throws \LdapRecord\ContainerException
	 */
	public function attemptLogin(Request $request)
	{
		try {
			$attempt = $this->guard()->attempt(
				$this->credentials($request), $request->boolean('remember')
			);

		} catch (\Throwable $e) {
			Log::error(sprintf('%s:! Authentication may have succeeded, but an error [%s] was thrown',self::LOGKEY,$e->getMessage()));
			return FALSE;
		}

		// If the login failed, and PLA is set to use DN login, check if the entry exists.
		// If the entry doesnt exist, it might be the root DN, which cannot be used to login
		if ((! $attempt) && $request->dn && config('pla.login.alert_rootdn',TRUE)) {
			// Double check our credentials, and see if they authenticate
			try {
				Container::getInstance()
					->getConnection()
					->auth()
					->bind($request->get(login_attr_name()),$request->get('password'));

			} catch (BindException $e) {
				// Password incorrect, fail anyway
				return FALSE;
			}

			$dn = config('server')->fetch($request->dn);
			$o = new Entry;

			if ((! $dn) && $o->getConnection()->getLdapConnection()->errNo() === 32)
				abort(501,'Authentication succeeded, but the DN doesnt exist');
		}

		return $attempt;
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
		$this->guard()->logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

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

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function username()
	{
		return login_attr_name();
	}
}