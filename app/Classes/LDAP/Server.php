<?php

namespace App\Classes\LDAP;

use App\Ldap\Entry;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Server
{
	/**
	 * Query the server for a DN
	 *
	 * @param string $dn
	 * @return array|\LdapRecord\Query\Collection|null
	 */
	public function children(string $dn)
	{
		try {
			return ($x=(new Entry)
				->query()
				->select(['dn','hassubordinates'])
				->setDn($dn)
				->listing()
				->get()) ? $x : NULL;

		// @todo Tidy up this exception
		} catch (\Exception $e) {
			dd(['e'=>$e]);
		}
	}

	/**
	 * Fetch a DN from the server
	 *
	 * @param string $dn
	 * @param array $attrs
	 * @return array|\LdapRecord\Models\Model|\LdapRecord\Query\Collection|\LdapRecord\Query\Model\Builder|null
	 */
	public function fetch(string $dn,array $attrs=['*','+'])
	{
		try {
			return ($x=(new Entry)
				->query()
				->select($attrs)
				->find($dn)) ? $x : NULL;

		// @todo Tidy up this exception
		} catch (\Exception $e) {
			dd(['e'=>$e]);
		}
	}

	/**
	 * Gets the root DN of the specified LDAPServer, or NULL if it
	 * can't find it (ie, the server won't give it to us, or it isnt
	 * specified in the configuration file).
	 *
	 * @return Collection|null array|NULL The root DN(s) of the server on success (string) or NULL if it cannot be determine.
	 * @todo Sort the entries, so that they are in the correct DN order.
	 */
	public function getBaseDN(): ?Collection
	{
		//findBaseDn()?
		// If the base is set in the configuration file, then just return that after validating it exists.
		// @todo
		if (false) {

		// We need to work out the baseDN
		} else {
			$result = $this->getDNAttrValues('',['namingcontexts']);

			return $result ? collect($result->namingcontexts) : NULL;
		}
	}

	/**
	 * Search for a DN and return its attributes
	 *
	 * @param $dn
	 * @param array $attrs
	 * @param int $deref		 // @todo
	 * @return Entry|bool
	 */
	protected function getDNAttrValues(string $dn,array $attrs=['*','+'],int $deref=LDAP_DEREF_NEVER): ?Entry
	{
		try {
			return ($x=(new Entry)
				->query()
				->select($attrs)
				->find($dn)) ? $x : NULL;

		// @todo Tidy up this exception
		} catch (\Exception $e) {
			dd(['e'=>$e]);
		}
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
	 */
	public static function getOID(string $oid,string $key): ?string
	{
		$oids = Cache::remember('oids',86400,function() {

			try {
				$f = fopen(config_path('ldap_supported_oids.txt'),'r');

			} catch (\Exception $e) {
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
			($key == 'desc' ? 'No description available, can you help with one?' : ($key == 'title' ? $oid : ''))
		);
	}
}
