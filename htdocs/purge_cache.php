<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/purge_cache.php,v 1.9.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $_SESSION[APPCONFIG]->isCommandAvailable('purge'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('purge')),'error','index.php');

$purge_session_keys = array('cache');

$size = 0;
foreach ($purge_session_keys as $key) {
	if (isset($_SESSION[$key])) {
		$size += strlen(serialize($_SESSION[$key]));
		unset($_SESSION[$key]);
	}
}

if (! $size)
	$body =  _('No cache to purge.');
else
	$body = sprintf(_('Purged %s bytes of cache.'),number_format($size));

system_message(array(
	'title'=>_('Purge cache'),
	'body'=>$body,
	'type'=>'info'),
	'index.php');
?>
