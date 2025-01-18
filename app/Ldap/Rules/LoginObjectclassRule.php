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
    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
		if ($x=config('pla.login.objectclass')) {
			return count(array_intersect($user->objectclass,$x));

		// Otherwise allow the user to login
		} else {
			return TRUE;
		}
    }
}
