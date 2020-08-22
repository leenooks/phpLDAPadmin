<?php

namespace Leenooks\LDAP;

use Adldap\Adldap;
use Adldap\Models\Entry;

class Server
{
	/**
	 * Gets the root DN of the specified LDAPServer, or NULL if it
	 * can't find it (ie, the server won't give it to us, or it isnt
	 * specified in the configuration file).
	 *
	 * @return array array|NULL The root DN(s) of the server on success (string) or NULL if it cannot be determine.
	 * @todo Sort the entries, so that they are in the correct DN order.
	 */
	public function getBaseDN(): ?array
	{
		// If the base is set in the configuration file, then just return that after validating it exists.
		// @todo
		if (false) {

		// We need to work out the baseDN
		} else {
			$result = $this->getDNAttrValues('',['namingcontexts']);

			return $result ? $result->namingcontexts : NULL;
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
			return ($x=(new Adldap)
				->addProvider(config('ldap.connections.default.settings'))
				->search()
				->select($attrs)
				->findByDn($dn)) ? $x : NULL;

		// @todo Tidy up this exception
		} catch (\Exception $e) {
			dd(['e'=>$e,'s'=>$s]);
		}
	}
}