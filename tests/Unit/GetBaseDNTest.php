<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Ldap\Entry;

class GetBaseDNTest extends TestCase
{
	/**
	 * Test that we can get the Base DN of an LDAP server
	 *
	 * @return void
	 * @throws \LdapRecord\Models\ModelNotFoundException
	 * @covers \App\Ldap\Entry::baseDN()
	 */
	public function testBaseDNExists()
	{
		$o = (new Entry)->baseDN();

		$this->assertIsObject($o);
		$this->assertCount(1,$o->toArray());
		$this->assertContains('dc=Test',$o->toArray());
	}
}
