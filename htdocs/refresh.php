<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/refresh.php,v 1.18.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * This script alters the session variable 'tree', by re-querying
 * the LDAP server to grab the contents of every expanded container.
 *
 * @package phpLDAPadmin
 */
/**
 * @todo: Change this to just refresh all the open entries.
 */

require './common.php';

if (! $_SESSION[APPCONFIG]->isCommandAvailable('server_refresh'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('refresh server')),'error','index.php');

unset($_SESSION['cache'][$ldapserver->server_id]['tree']);

header(sprintf('Location: cmd.php?server_id=%s',$ldapserver->server_id));
die();
?>
