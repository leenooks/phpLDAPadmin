<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
		$this->assertTrue($this->login());
		$this->assertTrue(Auth::check());

		$this->assertTrue(Session::has('username_encrypt'));
		$this->assertTrue(Session::has('password_encrypt'));
	}
}