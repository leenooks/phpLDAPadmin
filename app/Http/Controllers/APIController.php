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

		return (new Server())
			->children($dn)
			->transform(function($item) {
				return [
					'title'=>$item->getRdn(),
					'item'=>Crypt::encryptString($item->getDn()),
					'icon'=>$item->icon(),
					'lazy'=>Arr::get($item->getAttribute('hassubordinates'),0) == 'TRUE',
					'tooltip'=>$item->getDn(),
				];
			});
	}
}
