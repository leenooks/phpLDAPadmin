<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

use App\Exceptions\InvalidUsage;
use App\Ldap\Entry;

class HomeController extends Controller
{
	private const LOGKEY = 'CHc';

	/**
	 * Render a frame, normally as a result of an AJAX call
	 * This will render the right frame.
	 *
	 * @param Request $request
	 * @param Collection|null $old
	 * @return View
	 * @throws InvalidUsage
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function frame(Request $request,?Collection $old=NULL): \Illuminate\View\View
	{
		// If our index was not render from a root url, then redirect to it
		if (($request->root().'/' !== url()->previous()) && $request->method() === 'POST')
			abort(409);

		$key = request_key($request);

		$view = $old
			? view('frame')->with('subframe',$key['cmd'])
			: view('frames.'.$key['cmd']);

		// If we are rendering a DN, rebuild our object
		if (in_array($key['cmd'],['create'])) {
			$o = new Entry;
			$o->setRDNBase($key['dn']);

		} elseif ($key['dn']) {
			// @todo Need to handle if DN is null, for example if the user's session expired and the ACLs dont let them retrieve $key['dn']
			$o = config('server')->fetch($key['dn']);
		}

		foreach (collect(old())->except(array_merge(EntryController::INTERNAL_POST,['dn'])) as $attr => $value)
			$o->{$attr} = $value;

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
				]))
				->with('updated',session()->pull('updated') ?: collect()),

			'import' => $view,

			default => abort(404),
		};
	}

	/**
	 * Show the Schema Viewer
	 *
	 * @note Our route will validate that types are valid.
	 * @param Request $request
	 * @return \Illuminate\View\View
	 * @throws InvalidUsage
	 */
	public function frame_schema(Request $request): \Illuminate\View\View
	{
		// If an invalid key, we'll 404
		if ($request->type && $request->get('_key') && (! config('server')->schema($request->type)->has($request->get('_key'))))
			abort(404);

		return view('frames.schema')
			->with('type',$request->type)
			->with('key',$request->get('_key'));
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