<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ImportTest extends TestCase
{
	public function testLDIF_Import()
	{
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.ldif';

		$this->assertTrue($this->login());
		$this->assertTrue(Auth::check());
		$this->actingAs(Auth::user());
		$this->assertFalse(config('ldap.cache.enabled'));

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		// Delete the entry
		$x->delete();
		$this->assertEquals(NULL,config('server')->fetch($dn));

		$file = new UploadedFile($import_file,'ldif-import.ldif',null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/import')
			->post('/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		//$response->dump();
		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
	}
}