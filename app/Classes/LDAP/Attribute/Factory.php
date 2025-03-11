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
		'creatorsname' => Internal\DN::class,
		'contextcsn' => Internal\CSN::class,
		'entrycsn' => Internal\CSN::class,
		'entrydn' => Internal\DN::class,
		'entryuuid' => Internal\UUID::class,
		'etag' => Internal\Etag::class,
		'gidnumber' => GidNumber::class,
		'hassubordinates' => Internal\HasSubordinates::class,
		'jpegphoto' => Binary\JpegPhoto::class,
		'modifytimestamp' => Internal\Timestamp::class,
		'modifiersname' => Internal\DN::class,
		'numsubordinates' => Internal\NumSubordinates::class,
		'objectclass' => ObjectClass::class,
		'pwdpolicysubentry' => Internal\PwdPolicySubentry::class,
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
		$class = Arr::get(self::map,strtolower($attribute),Attribute::class);
		Log::debug(sprintf('%s:Creating LDAP Attribute [%s] as [%s]',static::LOGKEY,$attribute,$class));

		return new $class($attribute,$values);
	}
}