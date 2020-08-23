<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Leenooks\LDAP\Server;

class GetBaseDNTest extends TestCase
{
    /**
     * Test that we can get the Base DN of an LDAP server
     *
     * @return void
     */
    public function testBaseDNExists()
    {
		$o = new Server;
		$x = $o->getBaseDN();

		$this->assertIsObject($x);
		$this->assertCount(1,$x->toArray());
		$this->assertContains('dc=Test',$x->toArray());
	}
}
