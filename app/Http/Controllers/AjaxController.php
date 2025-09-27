<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Server;

class AjaxController extends Controller
{
	private const LOGKEY = 'CAc';

	/**
	 * Get the LDAP server BASE DNs
	 *
	 * @return Collection
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 */
	public function bases(): Collection
	{
		return Server::baseDNs()
			->map(fn($item)=> [
				'title'=>$item->is_base ? $item->getDn() : $item->getRdn(),
				'item'=>$item->getDNSecure(),
				'lazy'=>TRUE,
				'icon'=>'fa-fw fas fa-sitemap',
				'tooltip'=>$item->getDn(),
			])->values();
	}

	/**
	 * @param Request $request
	 * @return Collection
	 */
	public function children(Request $request): Collection
	{
		$dn = Crypt::decryptString($request->post('_key'));

		// Sometimes our key has a command, so we'll ignore it
		if (str_starts_with($dn,'*') && ($x=strpos($dn,'|')))
			$dn = substr($dn,$x+1);

		Log::debug(sprintf('%s:Query [%s]',self::LOGKEY,$dn));

		return config('server')
			->children($dn)
			->transform(fn($item)=>
				[
					'title'=>$item->getRdn(),
					'item'=>$item->getDNSecure(),
					'icon'=>$item->icon(),
					'lazy'=>(strcasecmp(Arr::get($item->getAttribute('hassubordinates'),0),'TRUE') === 0)
						|| Arr::get($item->getAttribute('numsubordinates'),0),
					'tooltip'=>$item->getDn(),
				])
			->prepend(
				$request->create
					? [
						'title'=>sprintf('[%s]',__('Create Entry')),
						'item'=>Crypt::encryptString(sprintf('*%s|%s','create',$dn)),
						'lazy'=>FALSE,
						'icon'=>'fas fa-fw fa-square-plus text-warning',
						'tooltip'=>__('Create new LDAP item here'),
					]
					: []
			)
			->push(
				config('server')->hasMore()
					? [
						'title'=>sprintf('[%s]',__('Size Limit')),
						'item'=>'',
						'lazy'=>FALSE,
						'icon'=>'fas fa-fw fa-triangle-exclamation text-danger',
						'tooltip'=>__('There may be more entries'),
					]
					: []
			)
			->filter()
			->values();
	}

	public function schema_view(Request $request)
	{
		$server = new Server;

		switch($request->type) {
			case 'objectclasses':
				return view('fragment.schema.objectclasses')
					->with('objectclasses',$server->schema('objectclasses')->sortBy(fn($item)=>strtolower($item->name)));

			case 'attributetypes':
				return view('fragment.schema.attributetypes')
					->with('server',$server)
					->with('attributetypes',$server->schema('attributetypes')->sortBy(fn($item)=>strtolower($item->name)));

			case 'ldapsyntaxes':
				return view('fragment.schema.ldapsyntaxes')
					->with('ldapsyntaxes',$server->schema('ldapsyntaxes')->sortBy(fn($item)=>strtolower($item->description)));

			case 'matchingrules':
				return view('fragment.schema.matchingrules')
					->with('matchingrules',$server->schema('matchingrules')->sortBy(fn($item)=>strtolower($item->name)));

			default:
				abort(404);
		}
	}

	/**
	 * Return the required and additional attributes for an object class
	 *
	 * @param string $objectclass
	 * @return array
	 */
	public function schema_objectclass_attrs(Request $request,string $objectclass): array
	{
		$oc = config('server')->schema('objectclasses',$objectclass);
		$existing = $request->get('attrs',[]);

		return [
			'must' => $oc->getMustAttrs()
				->filter(fn($item)=>! $item->names->intersect($existing)->count())
				->pluck('name'),
			'may' => $oc->getMayAttrs()
				->filter(fn($item)=>! $item->names->intersect($existing)->count())
				->pluck('name'),
		];
	}

	public function subordinates(?string $dn=NULL): array
	{
		$dn = $dn ? Crypt::decryptString($dn) : '';

		// Sometimes our key has a command, so we'll ignore it
		if (str_starts_with($dn,'*') && ($x=strpos($dn,'|')))
			$dn = substr($dn,$x+1);

		$result = collect();
		// If no DN, we'll find all children
		if (! $dn)
			foreach (Server::baseDNs() as $base)
				$result = $result->merge(config('server')
					->subordinates($base->getDN()));
		else
			$result = config('server')
				->subordinates(collect(explode(',',$dn))->last());

		return
			$result->map(fn($item)=>['id'=>$item->getDNSecure(),'value'=>$item->getDN()])
			->toArray();
	}
}