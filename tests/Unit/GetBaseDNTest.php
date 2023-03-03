<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Classes\LDAP\Server;

class GetBaseDNTest extends TestCase
{
	/**
	 * Test that we can get the Base DN of an LDAP server
	 *
	 * @return void
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 * @covers \App\Classes\LDAP\Server::baseDNs()
	 */
	public function testBaseDnExists()
	{
		$o = Server::baseDNs();

		$this->assertIsObject($o);
		$this->assertCount(6,$o->toArray());
		$this->assertEquals('dc=Test',$o->first()->getDn());
	}
}
