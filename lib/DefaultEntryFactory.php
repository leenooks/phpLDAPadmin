<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/DefaultEntryFactory.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Allows to create new entries
 */
class DefaultEntryFactory extends EntryFactory {
	public function newEditingEntry($dn) {
		return new DefaultEditingEntry($dn);
	}

	public function newCreatingEntry($dn) {
		return new DefaultCreatingEntry();
	}
}
?>
