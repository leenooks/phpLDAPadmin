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
		'authorityrevocationlist' => Binary\CertificateList::class,
		'cacertificate' => Binary\Certificate::class,
		'certificaterevocationlist' => Binary\CertificateList::class,
		'createtimestamp' => Internal\Timestamp::class,
		'configcontext' => Schema\Generic::class,
		'krblastfailedauth' => Kerberos\Generic::class,
		'krblastpwdchange' => Kerberos\Generic::class,
		'krblastsuccessfulauth' => Kerberos\Generic::class,
		'krbpasswordexpiration' => Kerberos\Generic::class,
		'krbloginfailedcount' => Kerberos\Generic::class,
		'krbprincipalkey' => Kerberos\KrbPrincipalKey::class,
		'krbticketflags' => Kerberos\KrbTicketFlags::class,
		'gidnumber' => GidNumber::class,
		'jpegphoto' => Binary\JpegPhoto::class,
		'member' => Member::class,
		'modifytimestamp' => Internal\Timestamp::class,
		'monitorcontext' => Schema\Generic::class,
		'namingcontexts' => Schema\Generic::class,
		'objectclass' => ObjectClass::class,
		'sambaacctflags' => Samba\AcctFlags::class,
		'sambalmpassword' => Samba\LMPassword::class,
		'sambantpassword' => Samba\NTPassword::class,
		'supportedcontrol' => Schema\OID::class,
		'supportedextension' => Schema\OID::class,
		'supportedfeatures' => Schema\OID::class,
		'supportedldapversion' => Schema\Generic::class,
		'supportedsaslmechanisms' => Schema\Mechanisms::class,
		'uniquemember' => Member::class,
		'usercertificate' => Binary\Certificate::class,
		'userpassword' => Password::class,
		'pwdreset' => PwdReset::class,
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