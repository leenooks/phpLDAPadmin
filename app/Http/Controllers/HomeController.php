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

class HomeController extends Controller
{
	public function home()
	{
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

	public function info()
	{
		$attrs = collect((new Entry)->rootDSE()->getAttributes())
			->transform(function($item,$key) {
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

		return view('widgets.dn')
			->with('dn','Server Info')
			->with('attributes',$this->sortAttrs($attrs));
	}

	public function render(Request $request)
	{
		$dn = Crypt::decryptString($request->post('key'));

		return view('widgets.dn')
			->with('dn',$dn)
			->with('leaf',$x=(new Server())->fetch($dn))
			->with('attributes',$this->sortAttrs(collect($x->getAttributes())));
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
