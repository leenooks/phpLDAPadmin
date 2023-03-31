<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use LdapRecord\Exceptions\InsufficientAccessException;
use LdapRecord\LdapRecordException;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\Server;
use App\Exceptions\InvalidUsage;
use App\Http\Requests\EntryRequest;

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

	public function entry_update(EntryRequest $request)
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		foreach ($request->except(['_token','dn']) as $key => $value)
			$o->{$key} = array_filter($value);

		Session::put('dn',$request->dn);

		if (! $dirty=$o->getDirty())
			return back()->with(['note'=>__('No attributes changed')]);

		try {
			$o->update($request->except(['_token','dn']));

		} catch (InsufficientAccessException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 50:
					return back()->withErrors(sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}

		} catch (LdapRecordException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 8:
					return back()->withErrors(sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}
		}

		return back()
			->with(['success'=>__('Entry updated')])
			->with(['updated'=>$dirty]);
	}

	/**
	 * Application home page
	 */
	public function home()
	{
		$base = Server::baseDNs() ?: collect();

		$bases = $base->transform(function($item) {
			return [
				'title'=>$item->getRdn(),
				'item'=>$item->getDNSecure(),
				'lazy'=>TRUE,
				'icon'=>'fa-fw fas fa-sitemap',
				'tooltip'=>$item->getDn(),
			];
		});

		if (Session::has('dn'))
			return view('dn')
				->with('bases',$bases)
				->with('o',config('server')->fetch($dn=Crypt::decryptString(Session::pull('dn'))))
				->with('dn',$dn);
		else
			return view('home')
				->with('bases',$bases)
				->with('server',config('ldap.connections.default.name'));
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
