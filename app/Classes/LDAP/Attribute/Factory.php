<?php

namespace App\Classes\LDAP\Attribute;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use App\Classes\LDAP\Attribute;
use App\Ldap\Entry;

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
		'authorityrevocationlist' => Binary\CertificateList::class,
		'cacertificate' => Binary\Certificate::class,
		'certificaterevocationlist' => Binary\CertificateList::class,
		'createtimestamp' => Internal\Timestamp::class,
		'configcontext' => Schema\Generic::class,
		'krblastfailedauth' => Attribute\NoAttrTags\Generic::class,
		'krblastpwdchange' => Attribute\NoAttrTags\Generic::class,
		'krblastsuccessfulauth' => Attribute\NoAttrTags\Generic::class,
		'krbpasswordexpiration' => Attribute\NoAttrTags\Generic::class,
		'krbloginfailedcount' => Attribute\NoAttrTags\Generic::class,
		'krbprincipalkey' => KrbPrincipalKey::class,
		'krbticketflags' => KrbTicketFlags::class,
		'gidnumber' => GidNumber::class,
		'jpegphoto' => Binary\JpegPhoto::class,
		'member' => Member::class,
		'modifytimestamp' => Internal\Timestamp::class,
		'monitorcontext' => Schema\Generic::class,
		'namingcontexts' => Schema\Generic::class,
		'objectclass' => ObjectClass::class,
		'supportedcontrol' => Schema\OID::class,
		'supportedextension' => Schema\OID::class,
		'supportedfeatures' => Schema\OID::class,
		'supportedldapversion' => Schema\Generic::class,
		'supportedsaslmechanisms' => Schema\Mechanisms::class,
		'usercertificate' => Binary\Certificate::class,
		'userpassword' => Password::class,
	];

	/**
	 * Create the new Object for an attribute
	 *
	 * @param string $dn
	 * @param string $attribute
	 * @param array $values
	 * @param array $oc
	 * @return Attribute
	 */
	public static function create(string $dn,string $attribute,array $values,array $oc=[]): Attribute
	{
		$class = Arr::get(self::map,strtolower($attribute),Attribute::class);

		Log::debug(sprintf('%s:Creating Attribute [%s] for [%s] using class [%s]',self::LOGKEY,$attribute,$dn,$class),['values'=>$values]);
		return new $class($dn,$attribute,$values,$oc);
	}
}