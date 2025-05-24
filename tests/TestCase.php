<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use App\Classes\LDAP\Server;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication;

	protected function setUp(): void
	{
		parent::setUp();
		Config::set('server',new Server());
	}

	/**
	 * Hack to get testing working
	 */
	protected function tearDown(): void
	{
		$config = app('config');
		$events = app('events');
		parent::tearDown();
		app()->instance('config', $config);
		app()->instance('events', $events);
	}

	protected function login(): bool
	{
		//$username = 'cn=AdminUser,dc=Test';
		$username = 'admin';
		$password = 'password';

		$this->post('/login',['uid'=>$username,'password'=>$password]);

		return Auth::check() && (Auth::user()->getDN() === 'cn=AdminUser,dc=Test');
	}
}
