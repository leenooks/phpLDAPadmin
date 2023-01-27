<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Ldap\Entry;

class GetBaseDNTest extends TestCase
{
	/**
	 * Test that we can get the Base DN of an LDAP server
	 *
	 * @return void
	 * @throws \LdapRecord\Query\ObjectNotFoundException
	 * @covers \App\Ldap\Entry::baseDN()
	 */
	public function testBaseDnExists()
	{
		$o = (new Entry)->baseDN();

		$this->assertIsObject($o);
		$this->assertCount(6,$o->toArray());
		$this->assertEquals('dc=Test',$o->first()->getDn());
	}
}
