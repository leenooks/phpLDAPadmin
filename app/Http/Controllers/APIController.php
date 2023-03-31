<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use LdapRecord\Query\Collection;

use App\Classes\LDAP\Server;

class APIController extends Controller
{
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
				return view('frames.schema.objectclasses')
					->with('objectclasses',$server->schema('objectclasses')->sortBy(function($item) { return strtolower($item->name); }));

			case 'attributetypes':
				return view('frames.schema.attributetypes')
					->with('server',$server)
					->with('attributetypes',$server->schema('attributetypes')->sortBy(function($item) { return strtolower($item->name); }));

			case 'ldapsyntaxes':
				return view('frames.schema.ldapsyntaxes')
					->with('ldapsyntaxes',$server->schema('ldapsyntaxes')->sortBy(function($item) { return strtolower($item->description); }));

			case 'matchingrules':
				return view('frames.schema.matchingrules')
					->with('matchingrules',$server->schema('matchingrules')->sortBy(function($item) { return strtolower($item->name); }));

			default:
				abort(404);
		}
	}
}
