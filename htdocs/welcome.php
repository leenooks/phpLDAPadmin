<?php
// $Header$

/**
 * Show a simple welcome page.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

echo '<center>';
echo '<br /><br />';
printf('<img src="%s/logo.png" title="%s" alt="%s" />',IMGDIR,_('phpLDAPadmin logo'),_('phpLDAPadmin logo'));
echo '<br /><br />';
echo _('Use the menu to the left to navigate');
echo '<br /><br />';

$links = '';

if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links','credits'))
	$links .= sprintf('<a href="%s" target="_blank">%s</a>',get_href('credits'),_('Credits'));

if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links','help')) {
	if ($links) $links .= ' | ';
	$links .= sprintf('<a href="%s" target="_blank">%s</a>',get_href('documentation'),_('Documentation'));
}

if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links','donation')) {
	if ($links) $links .= ' | ';
	$links .= sprintf('<a href="%s" target="_blank">%s</a>',get_href('donate'),_('Donate'));
}

if ($links) {
	echo $links;
	echo '<br /><br />';
}

echo '</center>';
?>
