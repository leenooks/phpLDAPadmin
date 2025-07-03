<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use LdapRecord\Exceptions\InsufficientAccessException;
use LdapRecord\LdapRecordException;
use LdapRecord\Query\ObjectNotFoundException;
use Nette\NotImplementedException;

use App\Classes\LDAP\Attribute\{Factory,Password};
use App\Classes\LDAP\Import\LDIF as LDIFImport;
use App\Classes\LDAP\Export\LDIF as LDIFExport;
use App\Exceptions\Import\{GeneralException,VersionException};
use App\Exceptions\InvalidUsage;
use App\Http\Requests\{EntryRequest,EntryAddRequest,ImportRequest};
use App\Ldap\Entry;

class HomeController extends Controller
{
	private const LOGKEY = 'CHc';

	private const INTERNAL_POST = ['_auto_value','_key','_rdn','_rdn_new','_rdn_value','_step','_template','_token','_userpassword_hash'];

	/**
	 * Create a new object in the LDAP server
	 *
	 * @param EntryAddRequest $request
	 * @return \Illuminate\View\View
	 * @throws InvalidUsage
	 */
	public function entry_add(EntryAddRequest $request): \Illuminate\View\View
	{
		if (! old('_step',$request->validated('_step')))
			abort(404);

		$key = $this->request_key($request,collect(old()));

		$template = NULL;
		$o = new Entry;
		$o->setRDNBase($key['dn']);

		foreach (collect(old())->except(self::INTERNAL_POST) as $old => $value)
			$o->{$old} = array_filter($value);

		if (old('_template',$request->validated('template'))) {
			$template = $o->template(old('_template',$request->validated('template')));

			$o->objectclass = [Entry::TAG_NOTAG=>$template->objectclasses->toArray()];

			foreach ($o->getAvailableAttributes()
				 ->filter(fn($item)=>$item->names_lc->intersect($template->attributes->keys()->map('strtolower'))->count())
				 ->sortBy(fn($item)=>Arr::get($template->order,$item->name)) as $ao)
			{
				$o->{$ao->name} = [Entry::TAG_NOTAG=>''];
			}

		} elseif (count($x=collect(old('objectclass',$request->validated('objectclass')))->dot()->filter())) {
			$o->objectclass = Arr::undot($x);

			// Also add in our required attributes
			foreach ($o->getAvailableAttributes()->filter(fn($item)=>$item->is_must) as $ao)
				$o->{$ao->name} = [Entry::TAG_NOTAG=>''];
		}

		$step = $request->get('_step') ? $request->get('_step')+1 : old('_step');

		return view('frame')
			->with('subframe','create')
			->with('o',$o)
			->with('step',$step)
			->with('template',$template)
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
		$o = Factory::create(dn: $dn,attribute: $id,values: [],oc: $request->objectclasses);

		$view = $request->noheader
			? view(sprintf('components.attribute.widget.%s',$id))
				->with('value',$request->value)
				->with('loop',$xx)
			: view('components.attribute-type')
				->with('new',TRUE)
				->with('edit',TRUE);

		return $view
			->with('o',$o)
			->with('langtag',Entry::TAG_NOTAG)
			->with('template',NULL)
			->with('updated',FALSE);
	}

	public function entry_copy_move(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	{
		$from_dn = Crypt::decryptString($request->post('dn'));
		Log::info(sprintf('%s:Renaming [%s] to [%s]',self::LOGKEY,$from_dn,$request->post('to_dn')));

		$o = clone config('server')->fetch($from_dn);

		if (! $o)
			return back()
				->withInput()
				->with('note',__('DN doesnt exist'));

		$o->setDN($request->post('to_dn'));
		$o->exists = FALSE;

		// Add the RDN attribute to match the new RDN
		$rdn = collect(explode(',',$request->post('to_dn')))->first();

		list($attr,$value) = explode('=',$rdn);
		$o->{$attr} = [Entry::TAG_NOTAG => $o->getObject($attr)->tagValuesOld(Entry::TAG_NOTAG)->push($value)->unique()];

		Log::info(sprintf('%s:Copying [%s] to [%s]',self::LOGKEY,$from_dn,$o->getDN()));

		try {
			$o->save();

		} catch (LdapRecordException $e) {
			return Redirect::to('/')
				->withInput(['_key'=>Crypt::encryptString('*dn|'.$from_dn)])
				->with('failed',sprintf('%s: %s - %s: %s',
					__('LDAP Server Error Code'),
					$e->getDetailedError()?->getErrorCode() ?: $e->getMessage(),
					$e->getDetailedError()?->getErrorMessage() ?: $e->getFile(),
					$e->getDetailedError()?->getDiagnosticMessage() ?: $e->getLine(),
				));
		}

		if ($request->post('delete') && $request->post('delete') === '1') {
			Log::info(sprintf('%s:Deleting [%s] after copy',self::LOGKEY,$from_dn));

			$x = $this->entry_delete($request);

			return ($x->getSession()->has('success'))
				? Redirect::to('/')
					->withInput(['_key'=>Crypt::encryptString('*dn|'.$o->getDN())])
					->with('success',__('Entry copied and deleted'))
				: $x;
		}

		return Redirect::to('/')
			->withInput(['_key'=>Crypt::encryptString('*dn|'.$o->getDN())])
			->with('success',__('Entry copied'));
	}

	public function entry_create(EntryAddRequest $request): \Illuminate\Http\RedirectResponse
	{
		$key = $this->request_key($request,collect(old()));

		$dn = sprintf('%s=%s,%s',$request->get('_rdn'),$request->get('_rdn_value'),$key['dn']);

		$o = new Entry;
		$o->setDn($dn);

		foreach ($request->except(self::INTERNAL_POST) as $key => $value)
			$o->{$key} = array_filter($value);

		// We need to process and encrypt the password
		if ($request->userpassword)
			$o->userpassword = $this->password(
				$o->getObject('userpassword'),
				$request->userpassword,
				$request->get('_userpassword_hash'));

		try {
			$o->save();

		} catch (InsufficientAccessException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 50:
					return Redirect::to('/')
						->withInput()
						->with('failed',sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}

		// @todo when we create an entry, and it already exists, enable a redirect to it
		} catch (LdapRecordException $e) {
			return Redirect::back()
				->withInput()
				->with('failed',sprintf('%s: %s - %s: %s',
					__('LDAP Server Error Code'),
					$e->getDetailedError()->getErrorCode(),
					$e->getDetailedError()->getErrorMessage(),
					$e->getDetailedError()->getDiagnosticMessage(),
				));
		}

		// If there are an _auto_value attributes, we need to invalid those
		foreach ($request->get('_auto_value',[]) as $attr => $value) {
			Log::debug(sprintf('%s:Removing auto_value attr [%s]',self::LOGKEY,$attr));
			Cache::delete($attr.':'.Session::id());
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
						->with('failed',sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}

		} catch (LdapRecordException $e) {
			$request->flash();

			switch ($x=$e->getDetailedError()->getErrorCode()) {
				case 8:
					return Redirect::to('/')
						->withInput()
						->with('failed',sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}
		}

		return Redirect::to('/')
			->with('success',sprintf('%s: %s',__('Deleted'),$dn));
	}

	public function entry_export(Request $request,string $id): \Illuminate\View\View
	{
		$dn = Crypt::decryptString($id);

		$result = Entry::query()
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
		$dn = $request->get('_key') ? Crypt::decryptString($request->dn) : '';
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
		foreach ($password->values->dot() as $key => $value) {
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

		foreach ($request->except(['_token','dn','_userpassword_hash','userpassword']) as $key => $value)
			$o->{$key} = array_filter($value,fn($item)=>! is_null($item));

		// @todo Need to handle incoming attributes that were modified by MD5Updates Trait (eg: jpegphoto)

		// We need to process and encrypt the password
		if ($request->userpassword)
			$o->userpassword = $this->password(
				$o->getObject('userpassword'),
				$request->userpassword,
				$request->get('_userpassword_hash'));

		if (! $o->getDirty())
			return Redirect::back()
				->withInput()
				->with('note',__('No attributes changed'));

		return view('update')
			->with('dn',$dn)
			->with('o',$o);
	}

	public function entry_rename(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	{
		$from_dn = Crypt::decryptString($request->post('dn'));
		Log::info(sprintf('%s:Renaming [%s] to [%s]',self::LOGKEY,$from_dn,$request->post('_rdn_new')));

		$o = config('server')->fetch($from_dn);

		if (! $o)
			return Redirect::back()
				->withInput()
				->with('note',__('DN doesnt exist'));

		try {
			$o->rename($request->post('_rdn_new'));

		} catch (\Exception $e) {
			return Redirect::to('/')
				->with('failed',$e->getMessage());
		}

		return Redirect::to('/')
			->withInput(['_key'=>Crypt::encryptString('*dn|'.$o->getDN())])
			->with('success',sprintf('%s: %s',__('Entry renamed'),$from_dn));
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
			return Redirect::back()
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
						->with('failed',sprintf('%s: %s (%s)',__('LDAP Server Error Code'),$x,__($e->getDetailedError()->getErrorMessage())));

				default:
					abort(599,$e->getDetailedError()->getErrorMessage());
			}

		} catch (LdapRecordException $e) {
			return Redirect::to('/')
				->withInput()
				->with('failed',sprintf('%s: %s - %s: %s',
					__('LDAP Server Error Code'),
					$e->getDetailedError()->getErrorCode(),
					$e->getDetailedError()->getErrorMessage(),
					$e->getDetailedError()->getDiagnosticMessage(),
				));
		}

		return Redirect::to('/')
			->withInput()
			->with('updated',collect($dirty)
				->map(fn($item,$key)=>$o->getObject(collect(explode(';',$key))->first()))
				->values()
				->unique());
	}

	/**
	 * Render a frame, normally as a result of an AJAX call
	 * This will render the right frame.
	 *
	 * @param Request $request
	 * @param Collection|null $old
	 * @return \Illuminate\View\View
	 * @throws InvalidUsage
	 */
	public function frame(Request $request,?Collection $old=NULL): \Illuminate\View\View
	{
		// If our index was not render from a root url, then redirect to it
		if (($request->root().'/' !== url()->previous()) && $request->method() === 'POST')
			abort(409);

		$key = $this->request_key($request,$old);

		$view = $old
			? view('frame')->with('subframe',$key['cmd'])
			: view('frames.'.$key['cmd']);

		// If we are rendering a DN, rebuild our object
		if ($key['cmd'] === 'create') {
			$o = new Entry;
			$o->setRDNBase($key['dn']);

		} elseif ($key['dn']) {
			// @todo Need to handle if DN is null, for example if the user's session expired and the ACLs dont let them retrieve $key['dn']
			$o = config('server')->fetch($key['dn']);

			foreach (collect(old())->except(array_merge(self::INTERNAL_POST,['dn'])) as $attr => $value)
				$o->{$attr} = $value;
		}

		return match ($key['cmd']) {
			'create' => $view
				->with('container',old('container',$key['dn']))
				->with('o',$o)
				->with('template',NULL)
				->with('step',1),

			'dn' => $view
				->with('dn',$key['dn'])
				->with('o',$o)
				->with('page_actions',collect([
					'create'=>($x=($o->getObjects()->except('entryuuid')->count() > 0)),
					'copy'=>$x,
					'delete'=>(! is_null($xx=$o->getObject('hassubordinates')->value)) && ($xx === 'FALSE'),
					'edit'=>$x,
					'export'=>$x,
				])),

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
			: view('home');
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
				Log::debug('Processing LDIF import',['data'=>$x,'import'=>$import]);
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
			->with('result',$result)
			->with('ldif',htmlspecialchars($x));
	}

	private function password(Password $po,array $values,array $hash): array
	{
		// We need to process and encrypt the password
		$passwords = [];

		foreach (Arr::dot($values) as $dotkey => $value) {
			// If the password is still the MD5 of the old password, then it hasnt changed
			if (($old=Arr::get($po,$dotkey)) && ($value === md5($old))) {
				$passwords[$dotkey] = $value;
				continue;
			}

			if ($value) {
				$type = Arr::get($hash,$dotkey);
				$passwords[$dotkey] = Password::hash_id($type)
					->encode($value);
			}
		}

		return Arr::undot($passwords);
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
		$key = ($x=$request->get('_key',old('_key')))
			? Crypt::decryptString($x)
			: NULL;

		// Determine if our key has a command
		if (str_contains($key,'|')) {
			$m = [];

			if (preg_match('/\*([a-z_]+)\|(.+)$/',$key,$m)) {
				$cmd = $m[1];
				$dn = ($m[2] !== '_NOP') ? $m[2] : NULL;
			}

		} elseif ($x=old('dn',$request->get('_key'))) {
			$cmd = 'dn';
			$dn = Crypt::decryptString($x);
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
		if ($request->type && $request->get('_key') && (! config('server')->schema($request->type)->has($request->get('_key'))))
			abort(404);

		return view('frames.schema')
			->with('type',$request->type)
			->with('key',$request->get('_key'));
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