<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/welcome.php,v 1.26.2.2 2007/12/26 09:26:32 wurley Exp $

/**
 * @package phpLDAPadmin
 */

/**
 * Show a simple welcome page.
 */

require './common.php';

echo '<center>';
echo '<br /><br />';
printf('<img src="images/logo.jpg" title="%s" alt="%s" />',_('phpLDAPadmin logo'),_('phpLDAPadmin logo'));
echo '<br /><br />';
echo _('Use the menu to the left to navigate');
echo '<br /><br />';

$links = '';

if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links')) {
	if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links', 'credits')) {
		$links .= sprintf('<a href="%s" target="new">%s</a>',get_href('credits'),_('Credits'));
	}

	if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links', 'help')) {
		if ($links) $links .= ' | ';
		$links .= sprintf('<a href="%s" target="new">%s</a>',get_href('documentation'),_('Documentation'));
	}

	if ($_SESSION[APPCONFIG]->isCommandAvailable('external_links', 'donation')) {
		if ($links) $links .= ' | ';
		$links .= sprintf('<a href="%s" target="new">%s</a>',get_href('donate'),_('Donate'));
	}
}

if ($links) {
	echo $links;
	echo '<br /><br />';
}

echo '</center>';
?>
