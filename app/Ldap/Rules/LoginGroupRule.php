<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

/**
 * User must have this group to login
 *
 * The group is defined by LDAP_LOGIN_GROUP
 */
class LoginGroupRule implements Rule
{
	private const LOGKEY = 'LGR';

    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
		if ($x=config('pla.login.group')) {
			$result = $user->groups()->exists($x);

			if (!$result)
				\Log::alert(sprintf('%s:User login denied for [%s], not using the approved group: %s',self::LOGKEY,$user->getDN(),$x));

			return $result;

			// Otherwise allow the user to login
		} else {
			\Log::debug(sprintf('%s:No login group rule, permitting login',self::LOGKEY));

			return TRUE;
		}
    }
}
