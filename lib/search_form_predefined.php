<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_predefined.php,v 1.8.4.2 2005/12/08 12:06:58 wurley Exp $

/**
 * @package phpLDAPadmin
 */

echo '<form action="search.php" method="get" class="search">';
echo '<input type="hidden" name="search" value="true" />';
echo '<input type="hidden" name="form" value="predefined" />';
printf('<input type="hidden" name="format" value="%s" />',$format);

echo '<table><tr><td>';

if (isset($_GET['predefined']))
	$selected_q_number = intval($_GET['predefined']);
else
	$selected_q_number = null;

printf('<center><b>%s</b><br />',_('Predefined Searches'));
printf('<small>(<a href="search.php?server_id=%s&amp;form=simple">%s</a> | <a href="search.php?server_id=%s&amp;form=advanced">%s</a>)</small>',
	$ldapserver->server_id,_('Simple Search Form'),
	$ldapserver->server_id,_('Advanced Search Form'));

echo '<br /><br />';

if (! isset($queries) || ! is_array($queries) || count($queries) == 0) {
	echo '<br />';
	echo _('No queries have been defined in config.php.');
	echo '<br /><br /><br />';
	echo '</center>';
	echo '</td></tr></table>';
	echo '</body></html>';
	die();

} else {

	printf('<small>%s: </small>',_('Select a predefined search'));
	echo '<select name="predefined">';

	foreach ($queries as $q_number => $q) {

		if ($selected_q_number === $q_number)
			$selected = ' selected';
		else
			$selected = '';

		printf('<option value="%s"%s>%s</option>',$q_number,$selected,htmlspecialchars($q['name']));
	}

	echo '</select>';

}

echo '<br /><br />';
printf('<center><input type="hidden" name="server_id" value="%s" /></center>',$ldapserver->server_id);
printf('<center><input type="submit" value="%s" /></center>',_('Search'));
echo '</center>';
echo '</td></tr></table></form>';
?>
