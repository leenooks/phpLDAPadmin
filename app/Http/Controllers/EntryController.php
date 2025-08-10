<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\ComponentAttributeBag;
use LdapRecord\Exceptions\InsufficientAccessException;
use LdapRecord\LdapRecordException;
use LdapRecord\Query\ObjectNotFoundException;
use Nette\NotImplementedException;

use App\Classes\LDAP\Attribute\Factory;
use App\Classes\LDAP\Import\LDIF as LDIFImport;
use App\Classes\LDAP\Export\LDIF as LDIFExport;
use App\Exceptions\Import\{GeneralException,VersionException};
use App\Exceptions\InvalidUsage;
use App\Http\Requests\{EntryRequest,EntryAddRequest,ImportRequest};
use App\Ldap\Entry;

class EntryController extends Controller
{
	private const LOGKEY = 'CEc';

	public const INTERNAL_POST = ['_auto_value','_key','_rdn','_rdn_new','_rdn_value','_step','_template','_token','template'];

	/**
	 * Create a new object in the LDAP server
	 *
	 * @param EntryAddRequest $request
	 * @return \Illuminate\View\View
	 * @throws InvalidUsage
	 */
	public function add(EntryAddRequest $request): \Illuminate\View\View
	{
		if (! old('_step',$request->validated('_step')))
			abort(404);

		$key = request_key($request->get('_key',old('_key')));

		$template = NULL;
		$o = new Entry;
		$o->setRDNBase($key['dn']);

		if (($oldpost=collect(old())->except(self::INTERNAL_POST))->count()) {
			foreach ($oldpost as $old => $value)
				$o->{$old} = $value;

		} else {
			if (old('_template',$request->validated('template'))) {
				$template = $o->templates->get(old('_template',$request->validated('template')));

				$o->objectclass = [Entry::TAG_NOTAG=>$template->objectclasses->toArray()];

				foreach ($o->getAvailableAttributes()
					 ->filter(fn($item)=>$item->names_lc->intersect($template->attributes->keys()->map('strtolower'))->count())
					 ->sortBy(fn($item)=>Arr::get($template->order,$item->name)) as $ao)
				{
					$o->{$ao->name} = [Entry::TAG_NOTAG=>['']];
				}

			} elseif (count($x=collect(old('objectclass',$request->validated('objectclass')))->dot()->filter())) {
				$o->objectclass = Arr::undot($x);

				// Also add in our required attributes
				foreach ($o->getAvailableAttributes()->filter(fn($item)=>$item->is_must) as $ao)
					$o->{$ao->name} = [Entry::TAG_NOTAG=>['']];
			}
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
	public function attr_add(Request $request,string $id): \Illuminate\View\View
	{
		$o = Factory::create(
			dn: $request->dn ? Crypt::decrypt($request->dn) : '',
			attribute: $id,
			oc: $request->objectclasses);

		$view = $request->noheader
			? view(sprintf('components.attribute.value.%s',$id))
				->with('value',$request->value)
			: view('components.attribute');

		return $view
			->with('o',$o)
			->with('edit',TRUE)
			->with('attrtag',Entry::TAG_NOTAG)
			->with('attributes',new ComponentAttributeBag(['class'=>'form-control mb-1']))
			->with('dotkey',sprintf('%s.%s',Entry::TAG_NOTAG,0))
			->with('template',NULL)
			->with('updated',FALSE);
	}

	public function copy_move(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	{
		$key = request_key($request->get('_key',old('_key')));
		$to_dn = $request->post('to_dn');
		Log::info(sprintf('%s:Renaming [%s] to [%s]',self::LOGKEY,$key['dn'],$request->post('to_dn')));

		$o = clone config('server')->fetch($key['dn']);
		$oldrdn = $o->rdn;

		if (! $o)
			return back()
				->withInput()
				->with('note',__('DN doesnt exist'));

		// @todo as part of this clone/setDN activity, should we clear the old_values, and automatically set exists to FALSE?
		$o->setDN($to_dn);
		$o->exists = FALSE;

		// Add the RDN attribute to match the new RDN
		$rdn = collect(explode(',',$to_dn))->first();

		list($attr,$value) = explode('=',$rdn);
		$o->{$attr} = [Entry::TAG_NOTAG => $o->getObject($attr)->tagValuesOld(Entry::TAG_NOTAG)->push($value)->unique()->toArray()];

		// Update the RDN attribute
		if ($oldrdn->rdn_value !== $o->rdn->rdn_value)
			$o->{$o->rdn->rdn_attr} = $o->getObject($o->rdn->rdn_attr)
				->values
				->dot()
				->diff($oldrdn->values->dot())
				->undot()
				->toArray();

		Log::info(sprintf('%s:Copying [%s] to [%s]',self::LOGKEY,$key['dn'],$o->getDN()));

		try {
			$o->save();

		} catch (LdapRecordException $e) {
			Log::alert(sprintf('%s:Copying failed with [%s], redirecting to edit [%s]',self::LOGKEY,$e->getMessage(),$to_dn));

			if ($request->post('delete') && $request->post('delete') === '1')
				Log::alert(sprintf('%s:Delete operation cancelled, DN [%s] not deleted',self::LOGKEY,$key['dn']));

			return Redirect::to('/')
				->withInput(
					$o->getObjects()
						->filter(fn($item)=>! $item->is_internal)
						->map(fn($item)=>$item->values_rendered->toArray())
						->merge(['_key'=>$o->getDNSecure('copy_move')])
						->toArray()
				)
				->with('failed',sprintf('%s: %s - %s: %s',
					__('LDAP Server Error Code'),
					$e->getDetailedError()?->getErrorCode() ?: $e->getMessage(),
					$e->getDetailedError()?->getErrorMessage() ?: $e->getFile(),
					$e->getDetailedError()?->getDiagnosticMessage() ?: $e->getLine(),
				));
		}

		if ($request->post('delete') && $request->post('delete') === '1') {
			Log::info(sprintf('%s:Deleting [%s] after copy',self::LOGKEY,$key['dn']));

			$x = $this->delete($request);

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

	public function create(EntryAddRequest $request): \Illuminate\Http\RedirectResponse
	{
		$key = request_key($request->get('_key',old('_key')));

		$dn = sprintf('%s=%s,%s',$request->get('_rdn'),$request->get('_rdn_value'),$key['dn']);

		$o = new Entry;
		$o->setDn($dn);

		foreach ($request->except(self::INTERNAL_POST) as $key => $value)
			$o->{$key} = array_filter($value);

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

	public function delete(Request $request): \Illuminate\Http\RedirectResponse
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

	public function export(Request $request,string $id): \Illuminate\View\View
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
	 * Process the incoming LDIF file or LDIF text
	 *
	 * @param ImportRequest $request
	 * @param string $type
	 * @return \Illuminate\View\View
	 * @throws GeneralException
	 * @throws VersionException
	 */
	public function import_process(ImportRequest $request,string $type): \Illuminate\View\View
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
			Log::error(sprintf('Import Exception [%s]',$e->getMessage()));

			abort(555,$e->getMessage());

		} catch (\Exception $e) {
			Log::error(sprintf('Import Exception [%s]',$e->getMessage()));

			abort(598,$e->getMessage());
		}

		return view('frame')
			->with('subframe','import_result')
			->with('result',$result)
			->with('ldif',htmlspecialchars($x));
	}

	/**
	 * Render an available list of objectclasses for an Entry
	 *
	 * @param Request $request
	 * @return Collection
	 */
	public function objectclass_add(Request $request): Collection
	{
		$dn = $request->get('_key') ? Crypt::decryptString($request->dn) : '';
		$oc = Factory::create(dn: $dn,attribute: 'objectclass',values: $request->oc);

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

	public function password_check(Request $request): Collection
	{
		$dn = Crypt::decryptString($request->dn);
		$o = config('server')->fetch($dn);
		$po = $o->getObject('userpassword');

		$result = collect();
		foreach ($request->password as $index => $value) {
			$key = Arr::get($request->password,$index.'.key');
			$form_value = Arr::get($request->password,$index.'.value');
			$password = $po->values->dot()->get($key);

			$hash = $po->hash($password);

			/*Log::debug(sprintf('comparing [%s] with [%s] type [%s]',
				$form_value,
				$password,
				$hash::id()),
				['object'=>$hash,'request'=>$request->password,'key'=>$key]);*/

			$result->put($key,(strlen($form_value) && $hash->compare($password,$form_value)) ? 'OK' :'FAIL');
		}

		return $result;
	}

	public function rename(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
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
	public function update_commit(EntryRequest $request): \Illuminate\Http\RedirectResponse
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
				->keys()
				->unique());
	}

	/**
	 * Show a confirmation to update a DN
	 *
	 * @param EntryRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 * @throws ObjectNotFoundException
	 */
	public function update_pending(EntryRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	{
		$dn = Crypt::decryptString($request->dn);

		$o = config('server')->fetch($dn);

		foreach ($request->except(['_token','dn']) as $key => $value)
			$o->{$key} = array_filter($value,fn($item)=>! is_null($item));

		if (! $o->getDirty())
			return Redirect::back()
				->withInput()
				->with('note',__('No attributes changed'));

		return view('update')
			->with('dn',$dn)
			->with('o',$o);
	}
}