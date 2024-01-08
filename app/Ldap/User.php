<?php

namespace App\Ldap;

use Laravel\Passport\HasApiTokens;
use LdapRecord\Models\OpenLDAP\User as Model;

use App\Ldap\Rules\LoginObjectclassRule;

class User extends Model
{
	use HasApiTokens;

	/**
	 * The object classes of the LDAP model.
	 *
	 * @note We set this to an empty array so that any objectclass can login
	 * @see LoginObjectclassRule::class
	 */
	public static array $objectClasses = [
	];

	/* METHODS */

	public function getDn(): string
	{
		return $this->exists ? parent::getDn() : 'Anonymous';
	}
}