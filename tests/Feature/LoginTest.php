<?php

namespace Tests\Feature;

use LdapRecord\Container;
use LdapRecord\Testing\DirectoryFake;
use Tests\TestCase;

class LoginTest extends TestCase
{
	public function test_user_can_view_a_login_form()
	{
		$response = $this->get('/login');

		$response->assertSuccessful();
		$response->assertViewIs('architect::auth.login');
	}

	public function test_admin_dn_login()
	{
		$username = 'cn=Admin,dc=Test';
		$password = 'test';

		//DirectoryFake::setup();

		$connection = Container::getDefaultConnection();
		$this->assertTrue($connection->auth()->attempt($username,$password));
	}
}
