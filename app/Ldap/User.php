<?php

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\User as Model;

use App\Exceptions\InvalidConfiguration;
use App\Ldap\Rules\LoginObjectclassRule;

class User extends Model
{
	/**
	 * The object classes of the LDAP model.
	 *
	 * @note We set this to an empty array so that any objectclass can login
	 * @see LoginObjectclassRule::class
	 */
	public static array $objectClasses = [];

	public function __construct(array $attributes = [])
	{
		$this->guidKey = config('pla.guidkey');

		parent::__construct($attributes);
	}

	/* METHODS */

	/**
	 * Override LdapRecord\Models\OpenLDAP\User::getAuthIdentifier, it throws a TypeError if the entry doesnt have a guidKey value
	 *
	 * @return string
	 * @throws InvalidConfiguration
	 */
	public function getAuthIdentifier(): string
	{
		if (! ($x=$this->getFirstAttribute($this->guidKey,'')))
			throw new InvalidConfiguration(sprintf('Entry didnt return any value for attribute: %s',$this->guidKey));

		return $x;
	}

	public function getDn(): string
	{
		return $this->exists ? parent::getDn() : 'Anonymous';
	}
}