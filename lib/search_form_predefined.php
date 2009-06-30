<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_predefined.php,v 1.10 2007/12/15 07:50:33 wurley Exp $

/**
 * @package phpLDAPadmin
 */

echo '<form action="cmd.php" method="get" class="search">';
echo '<input type="hidden" name="cmd" value="search" />';
echo '<input type="hidden" name="search" value="true" />';
echo '<input type="hidden" name="form" value="predefined" />';
printf('<input type="hidden" name="format" value="%s" />',$entry['format']);
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);

echo '<table class="search" border=0>';

if ($entry['predefined'])
	$selected_q_number = intval($entry['predefined']);
else
	$selected_q_number = null;

printf('<tr><td class="title" colspan=2>%s</td></tr>',_('Predefined Searches'));

$ss = $_SESSION['plaConfig']->isCommandAvailable('search', 'simple_search');
$as = $_SESSION['plaConfig']->isCommandAvailable('search', 'advanced_search');
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

if (! isset($_SESSION['plaConfig']->queries) || ! is_array($_SESSION['plaConfig']->queries) || count($_SESSION['plaConfig']->queries) == 0) {
	printf('<tr><td>%s</td></tr>',_('No queries have been defined in config.php.'));

} else {
	echo '<tr>';
	printf('<td><small>%s: </small></td>',_('Select a predefined search'));

	echo '<td>';
	echo '<select name="predefined">';

	foreach ($_SESSION['plaConfig']->queries as $q_number => $q) {
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
