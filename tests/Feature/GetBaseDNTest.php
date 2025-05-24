<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Classes\LDAP\Server;

class GetBaseDNTest extends TestCase
{
	/**
	 * Test that we can get the Base DN of an LDAP server
	 *
	 * @return void
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 */
	public function testBaseDnExists()
	{
		$o = Server::baseDNs(TRUE);

		$this->assertIsObject($o);
		$this->assertCount(6,$o->toArray());
		$this->assertEquals('c=AU',$o->first()->getDn());
	}
}