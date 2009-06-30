<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/refresh.php,v 1.15.4.2 2005/12/16 11:29:35 wurley Exp $

/**
 * This script alters the session variable 'tree', by re-querying
 * the LDAP server to grab the contents of every expanded container.
 *
 * Variables that come in via common.php
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 * @todo: Change this to just refresh all the open entries.
 */

require './common.php';

if (! isset($ldapserver))
	header('Location: tree.php');

unset($_SESSION['cache'][$ldapserver->server_id]['tree']);
pla_session_close();

header(sprintf('Location: tree.php#%s',$ldapserver->server_id));
?>
