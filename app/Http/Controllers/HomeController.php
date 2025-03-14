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
use Nette\NotImplementedException;

use App\Classes\LDAP\Attribute\{Factory,Password};
use App\Classes\LDAP\Server;
use App\Classes\LDAP\Import\LDIF as LDIFImport;
use App\Classes\LDAP\Export\LDIF as LDIFExport;
use App\Exceptions\Import\{GeneralException,VersionException};
use App\Exceptions\InvalidUsage;
use App\Http\Requests\{EntryRequest,EntryAddRequest,ImportRequest};
use App\Ldap\Entry;
use App\View\Components\AttributeType;

class HomeController extends Controller
{
	private function bases(): Collection
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
	 * Create a new object in the LDAP server
	 *
	 * @param EntryAddRequest $request
	 * @return View
	 * @throws InvalidUsage
	 */
	public function entry_add(EntryAddRequest $request): \Illuminate\View\View
	{
		if (! old('step',$request->validated('step')))
			abort(404);

		$key = $this->request_key($request,collect(old()));

		$o = new Entry;

		if (count(array_filter($x=old('objectclass',$request->objectclass)))) {
			$o->objectclass = $x;

			foreach($o->getAvailableAttributes()->filter(fn($item)=>$item->required) as $ao)
				$o->{$ao->name} = '';

			$o->setRDNBase($key['dn']);
		}

		$step = $request->step ? $request->step+1 : old('step');

		return view('frame')
			->with('subframe','create')
			->with('bases',$this->bases())
			->with('o',$o)
			->with('step',$step)
			->with('container',old('container',$key['dn']));
	}

	/**
	 * Render a new attribute view
	 *
	 * @param Request $request
	 * @param string $id
	 * @return \Illuminate\View\View
	 */
	public function entry_attr_add(Request $request,string $id): \Illuminate\View\View
	{
		$xx = new \stdClass;
		$xx->index = 0;

		$dn = $request->dn ? Crypt::decrypt($request->dn) : '';

		return $request->noheader
			? view(sprintf('components.attribute.widget.%s',$id))
				->with('o',Factory::create($dn,$id,[],$request->oc ?: []))
				->with('value',$request->value)
				->with('loop',$xx)
			: (new AttributeType(Factory::create($dn,$id,[],$request->oc ?: []),TRUE,collect($request->oc ?: [])))->render();
	}

	public function entry_create(EntryAddRequest $request): \Illuminate\Http\RedirectResponse
	{
		$key = $this->request_key($request,collect(old()));

		$dn = sprintf('%s=%s,%s',$request->rdn,$request->rdn_value,$key['dn']);

		$o = new Entry;
		$o->setDn($dn);

		foreach ($request->except(['_token','key','step','rdn','rdn_value']) as $key => $value)
			$o->{$key} = array_filter($value);

		try {
			$o->save();

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

		// @todo when we create an entry, and it already exists, enable a redirect to it
		} catch (LdapRecordException $e) {
            return Redirect::back()
                ->withInput()
                ->withErrors(sprintf('%s: %s - %s: %s',
                    __('LDAP Server Error Code'),
                    $e->getDetailedError()->getErrorCode(),
                    __($e->getDetailedError()->getErrorMessage()),
                    $e->getDetailedError()->getDiagnosticMessage(),
                ));
		}

		return Redirect::to('/')
			->withFragment($o->getDNSecure());
	}

	public function entry_delete(Request $request): \Illuminate\Http\RedirectResponse
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		try {
			$o->delete();

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
			->with('success',[sprintf('%s: %s',__('Deleted'),$dn)]);
	}

	public function entry_export(Request $request,string $id): \Illuminate\View\View
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

	/**
	 * Render an available list of objectclasses for an Entry
	 *
	 * @param Request $request
	 * @return Collection
	 */
	public function entry_objectclass_add(Request $request): Collection
	{
		$dn = $request->key ? Crypt::decryptString($request->dn) : '';
		$oc = Factory::create($dn,'objectclass',$request->oc);

		$ocs = $oc
			->structural
			->map(fn($item)=>$item->getParents())
			->flatten()
			->merge(
				config('server')->schema('objectclasses')
					->filter(fn($item)=>$item->isAuxiliary())
			)
			// Remove the original objectlcasses
			->filter(fn($item)=>(! $oc->values->contains($item)))
			->sortBy(fn($item)=>$item->name);

		return $ocs->groupBy(fn($item)=>$item->isStructural())
			->map(fn($item,$key) =>
				[
					'text' => sprintf('%s Object Class',$key ? 'Structural' : 'Auxiliary'),
					'children' => $item->map(fn($item)=>['id'=>$item->name,'text'=>$item->name]),
				]);
	}

	public function entry_password_check(Request $request): Collection
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
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 * @throws ObjectNotFoundException
	 */
	public function entry_pending_update(EntryRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		foreach ($request->except(['_token','dn','userpassword_hash','userpassword']) as $key => $value)
			$o->{$key} = array_filter($value,fn($item)=>! is_null($item));

		// @todo Need to handle incoming attributes that were modified by MD5Updates Trait (eg: jpegphoto)

		// We need to process and encrypt the password
		if ($request->userpassword) {
			$passwords = [];
			foreach ($request->userpassword as $key => $value) {
				// If the password is still the MD5 of the old password, then it hasnt changed
				if (($old=Arr::get($o->userpassword,$key)) && ($value === md5($old))) {
					array_push($passwords,$old);
					continue;
				}

				if ($value) {
					$type = Arr::get($request->userpassword_hash,$key);
					array_push($passwords,Password::hash_id($type)->encode($value));
				}
			}
			$o->userpassword = $passwords;
		}

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
	 * @todo When removing an attribute value, from a multi-value attribute, we have a ghost record showing after the update
	 * @todo Need to check when removing a single attribute value, do we have a ghost as well? Might be because we are redirecting with input?
	 */
	public function entry_update(EntryRequest $request): \Illuminate\Http\RedirectResponse
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
            return Redirect::to('/')
                ->withInput()
                ->withErrors(sprintf('%s: %s - %s: %s',
                    __('LDAP Server Error Code'),
                    $e->getDetailedError()->getErrorCode(),
                    __($e->getDetailedError()->getErrorMessage()),
                    $e->getDetailedError()->getDiagnosticMessage(),
                ));
		}

		return Redirect::to('/')
			->withInput()
			->with('updated',collect($dirty)->map(fn($key,$item)=>$o->getObject($item)));
	}

	/**
	 * Render a frame, normally as a result of an AJAX call
	 * This will render the right frame.
	 *
	 * @param Request $request
	 * @param Collection|null $old
	 * @return \Illuminate\View\View
	 */
	public function frame(Request $request,?Collection $old=NULL): \Illuminate\View\View
	{
		// If our index was not render from a root url, then redirect to it
		if (($request->root().'/' !== url()->previous()) && $request->method() === 'POST')
			abort(409);

		$key = $this->request_key($request,$old);

		$view = ($old
			? view('frame')->with('subframe',$key['cmd'])
			: view('frames.'.$key['cmd']))
			->with('bases',$this->bases());

		// If we are rendering a DN, rebuild our object
		$o = config('server')->fetch($key['dn']);

		// @todo We need to dynamically exclude request items, so we dont need to add them here
		foreach (collect(old())->except(['dn','_token','userpassword_hash']) as $attr => $value)
			$o->{$attr} = $value;

		return match ($key['cmd']) {
			'create' => $view
				->with('container',old('container',$key['dn']))
				->with('step',1),

			'dn' => $view
				->with('dn',$key['dn'])
				->with('o',$o)
				->with('page_actions',collect(['edit'=>TRUE])),

			'import' => $view,

			default => abort(404),
		};
	}

	/**
	 * This is the main page render function
	 */
	public function home(Request $request): \Illuminate\View\View
	{
		// Did we come here as a result of a redirect
		return count(old())
			? $this->frame($request,collect(old()))
			: view('home')
				->with('bases',$this->bases());
	}

	/**
	 * Process the incoming LDIF file or LDIF text
	 *
	 * @param ImportRequest $request
	 * @param string $type
	 * @return \Illuminate\View\View
	 * @throws GeneralException
	 * @throws VersionException
	 */
	public function import(ImportRequest $request,string $type): \Illuminate\View\View
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

	/**
	 * For any incoming request, work out the command and DN involved
	 *
	 * @param Request $request
	 * @param Collection|null $old
	 * @return array
	 */
	private function request_key(Request $request,?Collection $old=NULL): array
	{
		// Setup
		$cmd = NULL;
		$dn = NULL;
		$key = $request->get('key',old('key'))
			? Crypt::decryptString($request->get('key',old('key')))
			: NULL;

		// Determine if our key has a command
		if (str_contains($key,'|')) {
			$m = [];

			if (preg_match('/\*([a-z_]+)\|(.+)$/',$key,$m)) {
				$cmd = $m[1];
				$dn = ($m[2] !== '_NOP') ? $m[2] : NULL;
			}

		} elseif (old('dn',$request->get('key'))) {
			$cmd = 'dn';
			$dn = Crypt::decryptString(old('dn',$request->get('key')));
		}

		return ['cmd'=>$cmd,'dn'=>$dn];
	}

	/**
	 * Show the Schema Viewer
	 *
	 * @note Our route will validate that types are valid.
	 * @param Request $request
	 * @return \Illuminate\View\View
	 * @throws InvalidUsage
	 */
	public function schema_frame(Request $request): \Illuminate\View\View
	{
		// If an invalid key, we'll 404
		if ($request->type && $request->key && (! config('server')->schema($request->type)->has($request->key)))
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
	 * @return \Illuminate\Http\Response
	 */
	public function user_image(Request $request): \Illuminate\Http\Response
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