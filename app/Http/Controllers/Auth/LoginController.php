<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
