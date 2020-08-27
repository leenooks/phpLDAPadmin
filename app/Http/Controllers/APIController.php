<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Classes\LDAP\Server;

class APIController extends Controller
{
	/**
	 * Get the LDAP server BASE DNs
	 *
	 * @return array|null
	 */
	public function bases()
	{
		return (new Server())->getBaseDN()->transform(function($item) {
			return [
				'title'=>$item,
				'item'=>base64_encode(Crypt::encryptString($item)),
				//'folder'=>TRUE,
				'lazy'=>TRUE,
				//'key'=>0,
				//'autoexpand'=>TRUE,
			];
		});
	}
}
