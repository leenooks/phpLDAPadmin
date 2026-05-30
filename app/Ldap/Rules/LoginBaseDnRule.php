<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

/**
 * User must be in this end-dn to login
 *
 * The base DN is defined by LDAP_LOGIN_BASE_DN
 */
class LoginBaseDnRule implements Rule
{
	private const LOGKEY = 'LDNR';

    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
		if ($x=config('pla.login.base')) {
			$user_dn = $user->getDn();
			$result = str_ends_with($user_dn, $x);

			if (!$result)
				\Log::alert(sprintf('%s:User login denied for [%s], not in the base dn (%s)',self::LOGKEY,$user_dn,$x));

			return $result;

			// Otherwise allow the user to login
		} else {
			\Log::debug(sprintf('%s:No login base dn rule, permitting login',self::LOGKEY));

			return TRUE;
		}
    }
}
