<?php

namespace App\Ldap;

use Illuminate\Support\Collection;
use LdapRecord\Models\Model;
use LdapRecord\Models\ModelNotFoundException;
use LdapRecord\Query\Model\Builder;

class Entry extends Model
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [];

	/**
	 * Gets the root DN of the specified LDAPServer, or throws an exception if it
	 * can't find it.
	 *
	 * @param null $connection
	 * @return Collection
	 * @throws ModelNotFoundException
	 * @testedin GetBaseDNTest::testBaseDNExists();
	 */
    public function baseDN($connection = NULL): ?Collection
	{
		$base = static::on($connection ?? (new static)->getConnectionName())
			->in(NULL)
			->read()
			->select(['namingcontexts'])
			->whereHas('objectclass')
			->firstOrFail();

		return $base->namingcontexts ? collect($base->namingcontexts) : NULL;
	}

	/**
	 * Obtain the rootDSE for the server, that gives us server information
	 *
	 * @param null $connection
	 * @return Entry|null
	 * @throws ModelNotFoundException
	 * @testedin TranslateOidTest::testRootDSE();
	 */
	public function rootDSE($connection = NULL): ?Entry
	{
		return static::on($connection ?? (new static)->getConnectionName())
			->in(NULL)
			->read()
			->select(['+'])
			->whereHas('objectclass')
			->firstOrFail();
	}
}
