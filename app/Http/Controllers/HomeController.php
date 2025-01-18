<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use LdapRecord\Exceptions\InsufficientAccessException;
use LdapRecord\LdapRecordException;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\{Attribute,Server};
use App\Classes\LDAP\Import\LDIF as LDIFImport;
use App\Classes\LDAP\Export\LDIF as LDIFExport;
use App\Exceptions\Import\{GeneralException,VersionException};
use App\Exceptions\InvalidUsage;
use App\Http\Requests\{EntryRequest,ImportRequest};
use App\Ldap\Entry;
use App\View\Components\AttributeType;
use Nette\NotImplementedException;

class HomeController extends Controller
{
	private function bases()
	{
		$base = Server::baseDNs() ?: collect();

		return $base->transform(function($item) {
			return [
				'title'=>$item->getRdn(),
				'item'=>$item->getDNSecure(),
				'lazy'=>TRUE,
				'icon'=>'fa-fw fas fa-sitemap',
				'tooltip'=>$item->getDn(),
			];
		});
	}

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

		$page_actions = collect(['edit'=>TRUE,'copy'=>TRUE]);

		return view('frames.dn')
			->with('o',config('server')->fetch($dn))
			->with('dn',$dn)
			->with('page_actions',$page_actions);
	}

	public function entry_export(Request $request,string $id)
	{
		$dn = Crypt::decryptString($id);

		$result = (new Entry)
			->query()
			//->cache(Carbon::now()->addSeconds(Config::get('ldap.cache.time')))
			//->select(['*'])
			->setDn($dn)
			->recursive()
			->get();

		return view('fragment.export')
			->with('result',new LDIFExport($result));
	}

	public function entry_newattr(string $id)
	{
		$x = new AttributeType(new Attribute($id,[]),TRUE);
		return $x->render();
	}

	public function entry_password_check(Request $request)
	{
		$dn = Crypt::decryptString($request->dn);
		$o = config('server')->fetch($dn);

		$password = $o->getObject('userpassword');

		$result = collect();
		foreach ($password as $key => $value) {
			$hash = $password->hash($value);
			$compare = Arr::get($request->password,$key);
			//Log::debug(sprintf('comparing [%s] with [%s] type [%s]',$value,$compare,$hash::id()),['object'=>$hash]);

			$result->push((($compare !== NULL) && $hash->compare($value,$compare)) ? 'OK' :'FAIL');
		}

		return $result;
	}

	/**
	 * Show a confirmation to update a DN
	 *
	 * @param EntryRequest $request
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
	 * @throws ObjectNotFoundException
	 */
	public function entry_pending_update(EntryRequest $request)
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		foreach ($request->except(['_token','dn','userpassword_hash','userpassword']) as $key => $value)
			$o->{$key} = array_filter($value,fn($item)=>! is_null($item));

		// We need to process and encrypt the password
		$passwords = [];
		foreach ($request->userpassword as $key => $value) {
			// If the password is still the MD5 of the old password, then it hasnt changed
			if (($old=Arr::get($o->userpassword,$key)) && ($value === md5($old))) {
				array_push($passwords,$old);
				continue;
			}

			if ($value) {
				$type = Arr::get($request->userpassword_hash,$key);
				array_push($passwords,Attribute\Password::hash_id($type)->encode($value));
			}
		}
		$o->userpassword = $passwords;

		if (! $o->getDirty())
			return back()
				->withInput()
				->with('note',__('No attributes changed'));

		return view('update')
			->with('bases',$this->bases())
			->with('dn',$dn)
			->with('o',$o);
	}

	/**
	 * Update a DN entry
	 *
	 * @param EntryRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 * @throws ObjectNotFoundException
	 */
	public function entry_update(EntryRequest $request)
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		foreach ($request->except(['_token','dn']) as $key => $value)
			$o->{$key} = array_filter($value);

		if (! $dirty=$o->getDirty())
			return back()
				->withInput()
				->with('note',__('No attributes changed'));

		try {
			$o->update($request->except(['_token','dn']));

		} catch (InsufficientAccessException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 50:
					return Redirect::to('/')
						->withInput()
						->withErrors(sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}

		} catch (LdapRecordException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 8:
					return Redirect::to('/')
						->withInput()
						->withErrors(sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}
		}

		return Redirect::to('/')
			->withInput()
			->with('updated',collect($dirty)->map(fn($key,$item)=>$o->getObject($item)));
	}

	/**
	 * Application home page
	 */
	public function home()
	{
		if (old('dn'))
			return view('frame')
				->with('subframe','dn')
				->with('bases',$this->bases())
				->with('o',config('server')->fetch($dn=Crypt::decryptString(old('dn'))))
				->with('dn',$dn);

		elseif (old('frame'))
			return view('frame')
				->with('subframe',old('frame'))
				->with('bases',$this->bases());

		else
			return view('home')
				->with('bases',$this->bases())
				->with('server',config('ldap.connections.default.name'));
	}

	/**
	 * Process the incoming LDIF file or LDIF text
	 *
	 * @param ImportRequest $request
	 * @param string $type
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
	 * @throws GeneralException
	 * @throws VersionException
	 */
	public function import(ImportRequest $request,string $type)
	{
		switch ($type) {
			case 'ldif':
				$import = new LDIFImport($x=($request->text ?: $request->file->get()));
				break;

			default:
				abort(404,'Unknown import type: '.$type);
		}

		try {
			$result = $import->process();

		} catch (NotImplementedException $e) {
			abort(555,$e->getMessage());

		} catch (\Exception $e) {
			abort(598,$e->getMessage());
		}

		return view('frame')
			->with('subframe','import_result')
			->with('bases',$this->bases())
			->with('result',$result)
			->with('ldif',htmlspecialchars($x));
	}

	public function import_frame()
	{
		return view('frames.import');
	}

	/**
	 * LDAP Server INFO
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
	 */
	public function info()
	{
		return view('frames.info')
			->with('s',config('server'));
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
