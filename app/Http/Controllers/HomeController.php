<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

use App\Ldap\Entry;
use App\Classes\LDAP\Server;
use LdapRecord\Models\ModelNotFoundException;

class HomeController extends Controller
{
	/**
	 * Application home page
	 */
	public function home()
	{
		$base = (new Entry)->baseDN() ?: collect();

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
	 * @throws ModelNotFoundException
	 */
	public function info()
	{
		$root = (new Entry)->rootDSE();

		try {
			$attrs = collect($root->getAttributes())
				->transform(function($item) {
					foreach ($item as $k=>$v) {
						if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/',$v)) {
							$format = sprintf(
								'<abbr class="pb-1" title="%s"><i class="fas fa-list-ol pr-2"></i>%s</abbr>%s<p class="mb-0">%s</p>',
								$v,
								Server::getOID($v,'title'),
								($x=Server::getOID($v,'ref')) ? sprintf('<abbr class="pl-2" title="%s"><i class="fas fa-comment-dots"></i></abbr>',$x) : '',
								Server::getOID($v,'desc'),
						);
							$item[$k] = $format;
						}
					}
					return $item;
				});

		// @todo If we cant get server info, we should probably show a nice error dialog
		} catch (ModelNotFoundException $e) {
			$attrs = collect();
		}

		return view('frames.dn')
			->with('o',$root)
			->with('dn',__('Server Info'));
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
			->with('o',(new Server)->fetch($dn))
			->with('dn',$dn);
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
