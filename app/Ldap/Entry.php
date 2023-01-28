<?php

namespace App\Ldap;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Model;
use LdapRecord\Query\ObjectNotFoundException;

use App\Classes\LDAP\Attribute\Factory;

class Entry extends Model
{
    /**
     * The object classes of the LDAP model.
     *
     * @var array
     */
    public static $objectClasses = [];

	/* OVERRIDES */

	public function getAttributes(): array
	{
		$result = collect();
		foreach (parent::getAttributes() as $attribute => $value) {
			$result->put($attribute,Factory::create($attribute,$value));
		}

		return $result->toArray();
	}

	/* STATIC METHODS */

	/**
	 * Gets the root DN of the specified LDAPServer, or throws an exception if it
	 * can't find it.
	 *
	 * @param null $connection
	 * @return Collection
	 * @throws ObjectNotFoundException
	 * @testedin GetBaseDNTest::testBaseDNExists();
	 */
    public static function baseDNs($connection = NULL): ?Collection
	{
		$cachetime = Carbon::now()->addSeconds(Config::get('ldap.cache.time'));

		try {
			$base = static::on($connection ?? (new static)->getConnectionName())
				->cache($cachetime)
				->in(NULL)
				->read()
				->select(['namingcontexts'])
				->whereHas('objectclass')
				->firstOrFail();

		// If we cannot get to our LDAP server we'll head straight to the error page
		} catch (LdapRecordException $e) {
			abort(597,$e->getMessage());
		}

		/**
		 * @note While we are caching our baseDNs, it seems if we have more than 1,
		 * our caching doesnt generate a hit on a subsequent call to this function (before the cache expires).
		 * IE: If we have 5 baseDNs, it takes 5 calls to this function to case them all.
		 * @todo Possibly a bug wtih ldaprecord, so need to investigate
		 */
		$result = collect();
		foreach ($base->namingcontexts as $dn) {
			$result->push((new self)->cache($cachetime)->findOrFail($dn));
		}

		return $result;
	}

	/* ATTRIBUTES */

	/**
	 * Return a key to use for sorting
	 *
	 * @todo This should be the DN in reverse order
	 * @return string
	 */
	public function getSortKeyAttribute(): string
	{
		return $this->getDn();
	}

	/* METHODS */

	/**
	 * Return an icon for a DN based on objectClass
	 *
	 * @return string
	 */
	public function icon(): string
	{
		$objectclasses = array_map('strtolower',$this->objectclass);

		// Return icon based upon objectClass value
		if (in_array('person',$objectclasses) ||
			in_array('organizationalperson',$objectclasses) ||
			in_array('inetorgperson',$objectclasses) ||
			in_array('account',$objectclasses) ||
			in_array('posixaccount',$objectclasses))

			return 'fas fa-user';

		elseif (in_array('organization',$objectclasses))
			return 'fas fa-university';

		elseif (in_array('organizationalunit',$objectclasses))
			return 'fas fa-object-group';

		elseif (in_array('posixgroup',$objectclasses) ||
			in_array('groupofnames',$objectclasses) ||
			in_array('groupofuniquenames',$objectclasses) ||
			in_array('group',$objectclasses))

			return 'fas fa-users';

		elseif (in_array('dcobject',$objectclasses) ||
			in_array('domainrelatedobject',$objectclasses) ||
			in_array('domain',$objectclasses) ||
			in_array('builtindomain',$objectclasses))

			return 'fas fa-network-wired';

		elseif (in_array('alias',$objectclasses))
			return 'fas fa-theater-masks';

		elseif (in_array('country',$objectclasses))
			return sprintf('flag %s',strtolower(Arr::get($this->c,0)));

		elseif (in_array('device',$objectclasses))
			return 'fas fa-mobile-alt';

		elseif (in_array('document',$objectclasses))
			return 'fas fa-file-alt';

		elseif (in_array('iphost',$objectclasses))
			return 'fas fa-wifi';

		elseif (in_array('room',$objectclasses))
			return 'fas fa-door-open';

		elseif (in_array('server',$objectclasses))
			return 'fas fa-server';

		elseif (in_array('openldaprootdse',$objectclasses))
			return 'fas fa-info';

		// Default
		return 'fa-fw fas fa-cog';
	}

	/**
	 * Obtain the rootDSE for the server, that gives us server information
	 *
	 * @param null $connection
	 * @return Entry|null
	 * @throws ObjectNotFoundException
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
