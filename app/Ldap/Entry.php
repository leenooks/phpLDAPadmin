<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class Entry extends Model
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [];

	public function rootDSE($connection = null)
	{
		return static::on($connection ?? (new static)->getConnectionName())
			->in(null)
			->read()
			->select(['+'])
			->whereHas('objectclass')
			->firstOrFail();
	}
}
