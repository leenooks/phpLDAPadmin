<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

use App\Ldap\Entry;

class ImportTest extends TestCase
{
	// Test delete and create an entry
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

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		//$response->dump();
		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));
		$this->assertCount(4,$x->getObject('objectClass'));
		$this->assertCount(0,array_diff(['inetOrgPerson','posixAccount','top','shadowAccount'],$x->getObject('objectClass')->values->dot()->toArray()));
		$this->assertCount(1,$x->getObject('mail'));
		$this->assertContains(Entry::TAG_NOTAG.'.0',$x->getObject('mail')->values->dot()->keys());
		$this->assertContains('bart.simpson@example.com',$x->getObject('mail')->values->dot());
		$this->assertEquals(3024,strlen($x->getObject('jpegphoto')->values->dot()->first()));
	}

	public function testLDIF_Import_Replace() {
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.1.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertCount(1,$x->getObject('mail')->values->keys());
		$this->assertCount(2,$x->getObject('mail')->tagValues());
		$this->assertCount(0,array_diff(['barts@email.com','secondmail@example.com'],$x->getObject('mail')->values->dot()->values()->toArray()));

		$this->assertCount(1,$x->getObject('facsimiletelephonenumber')->values);
		$this->assertCount(1,$x->getObject('facsimiletelephonenumber')->tagValues());
		$this->assertCount(0,array_diff(['+1 555 222 4444'],$x->getObject('facsimiletelephonenumber')->values->dot()->values()->toArray()));
	}

	public function testLDIF_Import_Delete() {
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.2.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertCount(1,$x->getObject('mail')->values);
		$this->assertCount(2,$x->getObject('mail')->tagValues());
		$this->assertCount(0,array_diff(['barts@email.com','secondmail@example.com'],$x->getObject('mail')->values->dot()->values()->toArray()));

		$this->assertNull($x->getObject('facsimiletelephonenumber'));
	}

	public function testLDIF_Import_Append_Langtag() {
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.3.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertCount(3,$x->getObject('mail')->values->keys());
		$this->assertCount(4,$x->getObject('mail')->values->dot());
		$this->assertCount(2,$x->getObject('mail')->tagValues());
		$this->assertCount(1,$x->getObject('mail')->tagValues('lang-au'));
		$this->assertCount(1,$x->getObject('mail')->tagValues('lang-cn'));
		$this->assertCount(0,array_diff(['barts@email.com','secondmail@example.com','au-email@example.com','cn-email@example.com'],$x->getObject('mail')->values->dot()->values()->toArray()));
	}

	public function testLDIF_Import_Replace_Langtag() {
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.4.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertCount(3,$x->getObject('mail')->values);
		$this->assertCount(4,$x->getObject('mail')->values->dot());
		$this->assertCount(2,$x->getObject('mail')->tagValues());
		$this->assertCount(1,$x->getObject('mail')->tagValues('lang-au'));
		$this->assertCount(1,$x->getObject('mail')->tagValues('lang-cn'));
		$this->assertCount(0,array_diff(['notag@example.com','notag1@example.com','au-tag@example.com','cn-tag@example.com'],$x->getObject('mail')->values->dot()->values()->toArray()));
	}

	public function testLDIF_Import_Add_Base64()
	{
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.5.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertEquals(3396,strlen($x->getObject('jpegphoto')->values->dot()->first()));
	}

	public function testLDIF_Import_Replace_Base64()
	{
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.6.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertEquals(3024,strlen($x->getObject('jpegphoto')->values->dot()->first()));
	}

	public function testLDIF_Import_Multi() {
		$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
		$import_file = __DIR__.'/data/ldif-import.7.ldif';

		$this->assertTrue($this->login());

		// Check that it exists
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);

		$file = new UploadedFile($import_file,basename($import_file),null,null,true);

		$response = $this
			->actingAs(Auth::user())
			->from('/entry/import')
			->post('/entry/import/process/ldif',[
				'_token' => csrf_token(),
				'_key'=>Crypt::encryptString('*import|_NOP'),
				'file' => $file,
			]);

		$response->assertSuccessful();

		// Check that it hsa been created
		$this->assertEquals($dn,$x=config('server')->fetch($dn));
		$this->assertTrue($x->exists);
		$this->assertCount(4,$x->getObject('objectclass'));

		$this->assertCount(3,$x->getObject('mail')->values);
		$this->assertCount(6,$x->getObject('mail')->values->dot());
		$this->assertCount(2,$x->getObject('mail')->tagValues());
		$this->assertCount(2,$x->getObject('mail')->tagValues('lang-au'));
		$this->assertCount(2,$x->getObject('mail')->tagValues('lang-cn'));
		$this->assertCount(2,array_diff(['notag1@simpsons.example.com','notag2@simpsons.example.com','au-tag@simpsons.example.com','cn-tag@simpsons.example.com'],$x->getObject('mail')->values->dot()->values()->toArray()));
	}
}