<?php

namespace Tests\Feature;

use App\Classes\LDAP\Attribute;
use App\Ldap\Entry;
use Tests\TestCase;

/**
 * This unit will test Attributes that are:
 * + no_attr_tag attributes vs those with attr_tags, AND
 * + md5 attributes vs those that are not md5 attributes
 *
 * objectClass (a no_attr_tags_attribute)
 * userPassword (a no_attr_tags_attribute, and an md5 attribute)
 * certificate (a no_attr_tags attribute)
 * [internal attribute] (which is a no_attr_tags attribute)
 * mail (a normal attribute)
 *
 * => no_lang_tag attributes
 *    + ->values returns a Collection of values
 *    + ->values_old return a Collection of old values
 *    + ->tagValues() returns a Collection of values
 *    + ->tagValuesOld() return a Collection of old values
 *    + ->render_old_item() should be the raw value (unless an md5attribute, then the md5 value)
 *    + ->render_new_item() should be the raw value (unless an md5attribute, then the md5 value)
 *    + ->values is array with only 1 key _null_ with an array of values
 *    + ->values_old is array with only 1 key _null_ with an array of values
 *    + ->isDirty processing when there is a new value in the _null_ key and in another key (it should be ignored for no_attr_tags attributes)
 *    + ->isDirty processing when there is a new value, and its an md5 attribute
 *
 * The goal here is that
 * + no_attr_tags attributes return an array of values not indexed by an attr_tag
 * + attr_tag attributes are an array of values indexed by an attr_tag
 * + md5 attributes will render the md5 value, and compare the md5 value when determining if it has changed
 *
 * This will mean that our views then can render attributes with tagValues() and render_xxx_item() without just by calling
 * those methods with the langtag for the attribute rendering
 *
 * Attributes that are no_attr_tag attributes should not render anything in non-default langtag views
 */
class AttributeTagsTest extends TestCase
{
	private function read()
	{
		static $o = NULL;

		if (is_null($o)) {
			$dn = 'cn=Bart Simpson,ou=People,o=Simpsons';
			$this->assertTrue($this->login());
			$this->assertEquals($dn,$o=config('server')->fetch($dn));
		}

		return $o;
	}

	public function test_uid()
	{
		// Test UID, which can have attribute tags
		$o = $this->read();
		$new = ['newbart'];
		$o->uid = [
			'_null_' => $new,
		];

		$oo = $o->getObject('uid');

		$this->assertInstanceOf(Attribute::class,$oo);

		// ->values returns a Collection of values
		// ->values is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values);
		$this->assertArrayHasKey(Entry::TAG_NOTAG,$oo->values);
		$this->assertCount(1,$oo->values[Entry::TAG_NOTAG]);

		// ->values_old return a Collection of old values
		// ->values_old is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values_old);
		$this->assertArrayHasKey(Entry::TAG_NOTAG,$oo->values_old);
		$this->assertCount(1,$oo->values_old[Entry::TAG_NOTAG]);

		// ->tagValues() returns a Collection of values
		$this->assertCount(1,$oo->tagValues());

		// ->tagValuesOld() return a Collection of old values
		$this->assertCount(1,$oo->tagValuesOld());

		// ->render_item_old() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('bart',$oo->render_item_old(Entry::TAG_NOTAG.'.0'));
		// ->render_item_new() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('newbart',$oo->render_item_new(Entry::TAG_NOTAG.'.0'));

		// ->isDirty processing when there is a new value in the _null_ key and in another key (it should be ignored for no_attr_tags attributes)
		// ->isDirty processing when there is a new value, and its an md5 attribute
		$this->assertTrue($oo->isDirty());
		$this->assertCount(1,$x=$o->getDirty());
		$this->assertArrayHasKey('uid',$x);
		$this->assertCount(1,$x['uid']);
		$this->assertEquals($new,$x['uid']);
	}

	public function test_objectclass()
	{
		// Test ObjectClass, which can NOT have attribute tags
		$o = $this->read();
		$newoc = [
			'inetOrgPerson',
			'posixAccount',
			'top',
			'shadowAccount',
			'inetLocalMailRecipient',
		];

		$o->objectclass = [
			'_null_' => $newoc,
		];

		$oo = $o->getObject('objectclass');

		$this->assertInstanceOf(Attribute\ObjectClass::class,$oo);
		$this->assertTrue($oo->no_attr_tags);

		// ->values returns a Collection of values
		// ->values is array with only 1 key _null_ with an array of values
		$this->assertCount(5,$oo->values);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values);

		// ->values_old return a Collection of old values
		// ->values_old is array with only 1 key _null_ with an array of values
		$this->assertCount(4,$oo->values_old);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values_old);

		// ->tagValues() returns a Collection of values
		$this->assertCount(5,$oo->tagValues());

		// ->tagValuesOld() return a Collection of old values
		$this->assertCount(4,$oo->tagValuesOld());

		// ->render_item_old() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('inetOrgPerson',$oo->render_item_old('0'));
		// ->render_item_new() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('inetLocalMailRecipient',$oo->render_item_new('4'));

		// ->isDirty processing when there is a new value in the _null_ key and in another key (it should be ignored for no_attr_tags attributes)
		// ->isDirty processing when there is a new value, and its an md5 attribute
		$this->assertTrue($oo->isDirty());
		$this->assertCount(2,$x=$o->getDirty());
		$this->assertArrayHasKey('objectclass',$x);
		$this->assertCount(5,$x['objectclass']);
		$this->assertEquals($newoc,$x['objectclass']);
	}

	public function test_userpassword()
	{
		// Test ObjectClass, which can NOT have attribute tags
		$o = $this->read();
		$new = [
			'test1234',
		];
		$o->userpassword = [
			'_null_' => $new,
		];

		$oo = $o->getObject('userpassword');

		$this->assertInstanceOf(Attribute\Password::class,$oo);
		$this->assertTrue($oo->no_attr_tags);

		// ->values returns a Collection of values
		// ->values is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values);

		// ->values_old return a Collection of old values
		// ->values_old is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values_old);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values_old);

		// ->tagValues() returns a Collection of values
		$this->assertCount(1,$oo->tagValues());

		// ->tagValuesOld() return a Collection of old values
		$this->assertCount(1,$oo->tagValuesOld());

		// ->render_item_old() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('{*clear*}****************',$oo->render_item_old('0'));
		// ->render_item_new() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('****************',$oo->render_item_new('0'));

		// ->isDirty processing when there is a new value in the _null_ key and in another key (it should be ignored for no_attr_tags attributes)
		// ->isDirty processing when there is a new value, and its an md5 attribute
		$this->assertTrue($oo->isDirty());
		$this->assertCount(3,$x=$o->getDirty());
		$this->assertArrayHasKey('userpassword',$x);
		$this->assertCount(1,$x['userpassword']);
		$this->assertEquals($new,$x['userpassword']);
	}

	public function test_userpassword_nochange()
	{
		// Test ObjectClass, which can NOT have attribute tags
		$o = $this->read();
		$new = [
			'd88d98df6727f87376c93e9676978146',		// eatmyshorts
		];
		$o->userpassword = [
			'_null_' => $new,
		];

		$oo = $o->getObject('userpassword');

		$this->assertInstanceOf(Attribute\Password::class,$oo);
		$this->assertTrue($oo->no_attr_tags);

		// ->values returns a Collection of values
		// ->values is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values);

		// ->values_old return a Collection of old values
		// ->values_old is array with only 1 key _null_ with an array of values
		$this->assertCount(1,$oo->values_old);
		$this->assertArrayNotHasKey(Entry::TAG_NOTAG,$oo->values_old);

		// ->tagValues() returns a Collection of values
		$this->assertCount(1,$oo->tagValues());

		// ->tagValuesOld() return a Collection of old values
		$this->assertCount(1,$oo->tagValuesOld());

		// ->render_item_old() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('{*clear*}****************',$oo->render_item_old('0'));
		// ->render_item_new() should be the raw value (unless an md5attribute, then the md5 value)
		$this->assertEquals('****************',$oo->render_item_new('0'));

		// ->isDirty processing when there is a new value in the _null_ key and in another key (it should be ignored for no_attr_tags attributes)
		// ->isDirty processing when there is a new value, and its an md5 attribute
		$this->assertFalse($oo->isDirty());
		$this->assertCount(2,$x=$o->getDirty());
		$this->assertArrayNotHasKey('userpassword',$x);
	}
}