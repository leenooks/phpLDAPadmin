<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\Server;
use App\Exceptions\InvalidUsage;

class HomeController extends Controller
{
	/**
	 * Debug Page
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function debug()
	{
		return view('debug');
	}

	/**
	 * Application home page
	 */
	public function home()
	{
		$base = Server::baseDNs() ?: collect();

		return view('home')
			->with('server',config('ldap.connections.default.name'))
			->with('bases',$base->transform(function($item) {
				return [
					'title'=>$item->getRdn(),
					'item'=>Crypt::encryptString($item->getDn()),
					'lazy'=>TRUE,
					'icon'=>'fa-fw fas fa-sitemap',
					'tooltip'=>$item->getDn(),
				];
			}));
	}

	/**
	 * LDAP Server INFO
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 * @throws ObjectNotFoundException
	 */
	public function info()
	{
		// Load our attributes
		$s = config('server');
		$s->schema('objectclasses');
		$s->schema('attributetypes');

		return view('frames.info')
			->with('s',$s);
	}

	/**
	 * Render a specific DN
	 *
	 * @param Request $request
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function dn_frame(Request $request)
	{
		$dn = Crypt::decryptString($request->post('key'));

		return view('frames.dn')
			->with('o',config('server')->fetch($dn))
			->with('dn',$dn);
	}

	/**
	 * Show the Schema Viewer
	 *
	 * @note Our route will validate that types are valid.
	 * @param Request $request
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 * @throws InvalidUsage
	 */
	public function schema_frame(Request $request)
	{
		$s = config('server');

		// If an invalid key, we'll 404
		if ($request->type && $request->key && ($s->schema($request->type)->has($request->key) === FALSE))
			abort(404);

		return view('frames.schema')
			->with('type',$request->type)
			->with('key',$request->key);
	}

	/**
	 * Sort the attributes
	 *
	 * @param Collection $attrs
	 * @return Collection
	 */
	private function sortAttrs(Collection $attrs): Collection
	{
		return $attrs->sortKeys();
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
