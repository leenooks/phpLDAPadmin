<?php

namespace App\Classes\LDAP;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

use LdapRecord\Models\Model;
use LdapRecord\Query\Collection;
use LdapRecord\Query\Model\Builder;

use App\Ldap\Entry;

class Server
{
	/**
	 * Query the server for a DN and return it's children and if those children have children.
	 *
	 * @param string $dn
	 * @return array|Collection|null
	 */
	public function children(string $dn): ?Collection
	{
		return ($x=(new Entry)
			->query()
			->select(['*','hassubordinates'])
			->setDn($dn)
			->listing()
			->get()) ? $x : NULL;
	}

	/**
	 * Fetch a DN from the server
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return array|Model|Collection|Builder|null
	 */
	public function fetch(string $dn,array $attrs=['*','+']): ?Entry
	{
		return ($x=(new Entry)
			->query()
			->select($attrs)
			->find($dn)) ? $x : NULL;
	}

	/**
	 * Given an LDAP OID number, returns a verbose description of the OID.
	 * This function parses ldap_supported_oids.txt and looks up the specified
	 * OID, and returns the verbose message defined in that file.
	 *
	 * <code>
	 *  Array (
	 *    [title] => All Operational Attribute
	 *    [ref] => RFC 3673
	 *    [desc] => An LDAP extension which clients may use to request the return of all operational attributes.
	 *  )
	 * </code>
	 *
	 * @param string $oid The OID number (ie, "1.3.6.1.4.1.4203.1.5.1") of the OID of interest.
	 * @param string $key The title|ref|desc to return
	 * @return string|null
	 * @testedby TranslateOidTest::testRootDSE()
	 */
	public static function getOID(string $oid,string $key): ?string
	{
		$oids = Cache::remember('oids',86400,function() {
			try {
				$f = fopen(config_path('ldap_supported_oids.txt'),'r');

			} catch (Exception $e) {
				return NULL;
			}

			$result = collect();

			while (! feof($f)) {
				$line = trim(fgets($f));

				if (! $line OR preg_match('/^#/',$line))
					continue;

				$fields = explode(':',$line);

				$result->put(Arr::get($fields,0),[
					'title'=>Arr::get($fields,1),
					'ref'=>Arr::get($fields,2),
					'desc'=>Arr::get($fields,3),
				]);
			}
			fclose($f);

			return $result;
		});

		return Arr::get(
			($oids ? $oids->get($oid) : []),
			$key,
			($key == 'desc' ? 'No description available, can you help with one?' : ($key == 'title' ? $oid : NULL))
		);
	}

	public static function icon(Entry $dn): string
	{
		$objectclasses = array_map('strtolower',$dn->objectclass);

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

		elseif (in_array('dcobject',$objectclasses) ||
			in_array('domainrelatedobject',$objectclasses) ||
			in_array('domain',$objectclasses) ||
			in_array('builtindomain',$objectclasses))

			return 'fas fa-network-wired';

		elseif (in_array('country',$objectclasses))
			return sprintf('flag %s',strtolower(Arr::get($dn->c,0)));

		// Default
		return 'fa-fw fas fa-cog';
	}
}
