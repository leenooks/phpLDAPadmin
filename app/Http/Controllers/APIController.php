<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

use App\Classes\LDAP\Server;

class APIController extends Controller
{
	/**
	 * Get the LDAP server BASE DNs
	 *
	 * @return Collection
	 * @throws LdapRecord\Query\ObjectNotFoundException
	 */
	public function bases(): Collection
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
	 * @param Request $request
	 * @return Collection
	 */
	public function children(Request $request): Collection
	{
		$levels = $request->query('depth',1);
		$dn = Crypt::decryptString($request->query('key'));
		Log::debug(sprintf('%s: Query [%s] - Levels [%d]',__METHOD__,$dn,$levels));

		return (config('server'))
			->children($dn)
			->transform(function($item) {
				return [
					'title'=>$item->getRdn(),
					'item'=>$item->getDNSecure(),
					'icon'=>$item->icon(),
					'lazy'=>Arr::get($item->getAttribute('hassubordinates'),0) == 'TRUE',
					'tooltip'=>$item->getDn(),
				];
			});
	}

	public function schema_view(Request $request)
	{
		$server = new Server;

		switch($request->type) {
			case 'objectclasses':
				return view('fragment.schema.objectclasses')
					->with('objectclasses',$server->schema('objectclasses')->sortBy(function($item) { return strtolower($item->name); }));

			case 'attributetypes':
				return view('fragment.schema.attributetypes')
					->with('server',$server)
					->with('attributetypes',$server->schema('attributetypes')->sortBy(function($item) { return strtolower($item->name); }));

			case 'ldapsyntaxes':
				return view('fragment.schema.ldapsyntaxes')
					->with('ldapsyntaxes',$server->schema('ldapsyntaxes')->sortBy(function($item) { return strtolower($item->description); }));

			case 'matchingrules':
				return view('fragment.schema.matchingrules')
					->with('matchingrules',$server->schema('matchingrules')->sortBy(function($item) { return strtolower($item->name); }));

			default:
				abort(404);
		}
	}
}
