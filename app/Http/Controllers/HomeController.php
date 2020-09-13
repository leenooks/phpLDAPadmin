<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Classes\LDAP\Server;

class HomeController extends Controller
{
	public function home() {
		$o = new Server;

		return view('home')
			->with('server',config('ldap.connections.default.name'))
			->with('bases',$o->getBaseDN()->transform(function($item) {
				return [
					'title'=>$item,
					'item'=>Crypt::encryptString($item),
					'lazy'=>TRUE,
					'icon'=>'fa-fw fas fa-sitemap',
					'tooltip'=>$item,
				];
			}));
	}

	public function render(Request $request) {
		$dn = Crypt::decryptString($request->post('key'));

		return view('widgets.dn')
			->with('dn',$dn)
			->with('leaf',(new Server())->fetch($dn));
	}
}
