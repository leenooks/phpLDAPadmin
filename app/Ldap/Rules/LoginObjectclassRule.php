<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

/**
 * User must have this objectClass to login
 *
 * This is overridden by LDAP_LOGIN_OBJECTCLASS
 * @see User::$objectClasses
 */
class LoginObjectclassRule implements Rule
{
	private const LOGKEY = 'LOR';

	public function passes(LdapRecord $user,?Eloquent $model=NULL): bool
	{
		if ($x=config('pla.login.objectclass')) {
			$result = count(array_intersect(
				array_map('strtolower',$user?->objectclass ?: []),
				array_map('strtolower',$x)
			));

			if ($result === 0)
				\Log::alert(sprintf('%s:User login denied for [%s], not using an approved objectclass',self::LOGKEY,$user->getDN()));

			return $result > 0;

		// Otherwise allow the user to login
		} else {
			\Log::debug(sprintf('%s:No login rules, permitting login',self::LOGKEY));

			return TRUE;
		}
	}
}