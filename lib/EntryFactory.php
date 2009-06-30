<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/EntryFactory.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Allows to create new entries
 */
abstract class EntryFactory {
	public function newEntry($dn) {
		global $ldapserver;

		if ($dn && $ldapserver->dnExists($dn)) {
			return $this->newEditingEntry($dn);
		} else {
			return $this->newCreatingEntry($dn);
		}
	}

	abstract public function newEditingEntry($dn);
	abstract public function newCreatingEntry($dn);
}
?>
