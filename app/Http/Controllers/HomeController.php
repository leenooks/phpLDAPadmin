<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Leenooks\LDAP;

class HomeController extends Controller
{
	public function home() {
		$o = new LDAP\Server;

		return view('home')
			->with('server',config('ldap.connections.default.name'))		// @todo This connection name should be a config item
			->with('bases',$o->getBaseDN());
	}
}
