<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

use App\Classes\LDAP\Server;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
	public function home() {
		$o = new Server;

		return view('home')
			->with('server',config('ldap.connections.default.name'))
			->with('bases',$o->getBaseDN()->transform(function($item) {
				return [
					'title'=>$item,
					'item'=>Crypt::encryptString($item),
					'lazy'=>TRUE,
					'icon'=>'fa-fw fas fa-sitemap',
					'tooltip'=>$item,
				];
			}));
	}

	public function render(Request $request) {
		$dn = Crypt::decryptString($request->post('key'));

		return view('widgets.dn')
			->with('dn',$dn)
			->with('leaf',(new Server())->fetch($dn));
	}

	/**
	 * Return the image for the logged in user or anonymous
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function user_image(Request $request)
	{
		$image = NULL;
		$content = NULL;

		if (Auth::check()) {
			$image = Arr::get(Auth::user()->getAttribute('jpegphoto'),0);
			$content = 'image/jpeg';
		}

		if (! $image) {
			$image = File::get('../resources/images/user-secret-solid.svg');
			$content = 'image/svg+xml';
		}

		return response($image)
			->header('Content-Type',$content);
	}
}
