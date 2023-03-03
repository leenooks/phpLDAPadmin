<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\Attribute;

/**
 * This factory is used to return LDAP attributes as an object
 *
 * If there is no specific Attribute defined, then the default Attribute::class is return
 */
class Factory
{
	private const LOGKEY = 'LAf';

	/**
	 * Map of attributes to appropriate class
	 */
	public const map = [
		'createtimestamp' => Internal\Timestamp::class,
		'creatorsname' => Internal\EntryDN::class,
		'entrycsn' => Internal\EntryCSN::class,
		'entrydn' => Internal\EntryDN::class,
		'entryuuid' => Internal\EntryUUID::class,
		'gidnumber' => GidNumber::class,
		'hassubordinates' => Internal\HasSubordinates::class,
		'jpegphoto' => Binary\JpegPhoto::class,
		'modifytimestamp' => Internal\Timestamp::class,
		'modifiersname' => Internal\EntryDN::class,
		'objectclass' => ObjectClass::class,
		'structuralobjectclass' => Internal\StructuralObjectClass::class,
		'subschemasubentry' => Internal\SubschemaSubentry::class,
		'supportedcontrol' => Schema\OID::class,
		'supportedextension' => Schema\OID::class,
		'supportedfeatures' => Schema\OID::class,
		'supportedsaslmechanisms' => Schema\Mechanisms::class,
		'userpassword' => Password::class,
	];

	/**
	 * Create the new Object for an attribute
	 *
	 * @param string $attribute
	 * @param array $values
	 * @return Attribute
	 */
	public static function create(string $attribute,array $values): Attribute
	{
		$class = Arr::get(self::map,$attribute,Attribute::class);
		Log::debug(sprintf('%s:Creating LDAP Attribute [%s] as [%s]',static::LOGKEY,$attribute,$class));

		return new $class($attribute,$values);
	}
}