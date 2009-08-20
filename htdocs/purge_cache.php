<?php
/**
 * Purge our session cache details
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$purge_session_keys = array('app_initialized','backtrace','cache',APPCONFIG);

$size = 0;
foreach ($purge_session_keys as $key)
	if (isset($_SESSION[$key])) {
		$size += strlen(serialize($_SESSION[$key]));
		unset($_SESSION[$key]);
	}

if (! $size)
	$body = _('No cache to purge.');
else
	$body = sprintf(_('Purged %s bytes of cache.'),number_format($size));

system_message(array(
	'title'=>_('Purge cache'),
	'body'=>$body,
	'type'=>'info'),
	get_request('meth','REQUEST') == 'ajax' ? null : 'index.php');
?>
