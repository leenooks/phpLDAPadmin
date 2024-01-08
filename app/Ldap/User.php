<?php

namespace App\Ldap;

use Laravel\Passport\HasApiTokens;
use LdapRecord\Models\OpenLDAP\User as Model;

class User extends Model
{
	use HasApiTokens;

	/**
	 * The object classes of the LDAP model.
	 */
	public static array $objectClasses = [
		'posixAccount',
	];

	/* METHODS */

	public function getDn(): string
	{
		return $this->exists ? parent::getDn() : 'Anonymous';
	}
}