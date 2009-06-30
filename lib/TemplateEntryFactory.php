<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/TemplateEntryFactory.php,v 1.2 2007/12/15 07:50:32 wurley Exp $

/**
 * @package phpLDAPadmin
 * @author Xavier Bruyet
 *
 * Allows to create new entries
 */
class TemplateEntryFactory extends EntryFactory {
	public function newEditingEntry($dn) {
		return new TemplateEditingEntry($dn);
	}

	public function newCreatingEntry($dn) {
		return new TemplateCreatingEntry($dn);
	}
}
?>
