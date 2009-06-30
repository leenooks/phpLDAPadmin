<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_predefined.php,v 1.10.2.2 2008/01/13 05:37:02 wurley Exp $

/**
 * @package phpLDAPadmin
 */

printf('<h3 class="title">%s</h3>',_('Predefined Searches'));
echo '<br />';
echo '<form action="cmd.php">';
echo '<input type="hidden" name="cmd" value="search" />';
echo '<input type="hidden" name="search" value="true" />';
echo '<input type="hidden" name="form" value="predefined" />';
printf('<input type="hidden" name="format" value="%s" />',$entry['format']);
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

echo '<table class="forminput" border=0>';

if ($entry['predefined'])
	$selected_q_number = intval($entry['predefined']);
else
	$selected_q_number = null;


$ss = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'simple_search');
$as = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'advanced_search');
if ($ss | $as) {
	echo '<tr><td class="subtitle" colspan=2>(';
	if ($ss) {
		printf('<a href="cmd.php?cmd=search&amp;server_id=%s&amp;form=simple">%s</a>', $ldapserver->server_id,_('Simple Search Form'));
		if ($as) echo '	| ';
	}
	if ($as) {
		printf('<a href="cmd.php?cmd=search&amp;server_id=%s&amp;form=advanced">%s</a>', $ldapserver->server_id,_('Advanced Search Form'));
	}
	echo ')</td></tr>';
}

echo '<tr><td colspan=2>&nbsp;</td></tr>';

if (! isset($_SESSION[APPCONFIG]->queries) || ! is_array($_SESSION[APPCONFIG]->queries) || count($_SESSION[APPCONFIG]->queries) == 0) {
	printf('<tr><td>%s</td></tr>',_('No queries have been defined in config.php.'));

} else {
	echo '<tr>';
	printf('<td>%s:</td>',_('Select a predefined search'));

	echo '<td>';
	echo '<select name="predefined">';

	foreach ($_SESSION[APPCONFIG]->queries as $q_number => $q) {
		if ($selected_q_number === $q_number)
			$selected = ' selected';
		else
			$selected = '';

		printf('<option value="%s"%s>%s</option>',$q_number,$selected,htmlspecialchars($q['name']));
	}

	echo '</select>';
	echo '</td></tr>';

	echo '<tr><td colspan=2>&nbsp;</td></tr>';
	printf('<tr><td colspan=2><center><input type="submit" value="%s" /></center></td></tr>',_('Search'));
}

echo '</table></form>';
?>
