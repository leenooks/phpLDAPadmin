<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/purge_cache.php,v 1.9 2007/12/15 07:50:30 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $_SESSION['plaConfig']->isCommandAvailable('purge'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('purge')));

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
