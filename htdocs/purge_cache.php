<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/purge_cache.php,v 1.6.4.5 2005/12/16 11:29:35 wurley Exp $

/**
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include './header.php';

$purge_session_keys = array('cache');

echo '<body>';
echo '<h3 class="title">Purging Caches</h3><br /><br /><br />';

$size = 0;
foreach ($purge_session_keys as $key) {
	if (isset($_SESSION[$key])) {
		$size += strlen(serialize($_SESSION[$key]));
		unset($_SESSION[$key]);
	}
}
pla_session_close();

echo '<center>';
if (! $size)
	echo _('No cache to purge.');
else
	printf(_('Purged %s bytes of cache.'),number_format($size));

echo '</center>';

echo '<!-- refresh the tree view (with the new DN renamed)and redirect to the edit_dn page -->';
echo '<script type="text/javascript" language="javascript">parent.left_frame.location.reload();</script>';
echo '</body></html>';
?>
