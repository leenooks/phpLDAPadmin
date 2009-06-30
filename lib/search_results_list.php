<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_results_list.php,v 1.7.2.5 2008/11/30 13:19:49 wurley Exp $

/**
 * @package phpLDAPadmin
 */

# Iterate over each entry
$i = 0;

foreach ($results as $dn => $dndetails) {
	$i++;

	if ($i <= $start_entry)
		continue;

	if ($i >= $end_entry)
		break;

	echo '<table class="result" border=0>';

	echo '<tr class="list_title">';
	printf('<td class="icon"><img src="%s/%s" alt="icon" /></td>',IMGDIR,get_icon($ldapserver,$dn));

	$formatted_dn = get_rdn($dn);
	if (!$_SESSION[APPCONFIG]->isCommandAvailable('schema')) {
		$formatted_dn = explode('=', $formatted_dn, 2);
		$formatted_dn = $formatted_dn[1];
	}

	printf('<td colspan=2><a href="cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s">%s</a></td>',
		$ldapserver->server_id,rawurlencode(dn_unescape($dn)),htmlspecialchars($formatted_dn));
	echo '</tr>';

	if ($_SESSION[APPCONFIG]->isCommandAvailable('schema')) {
		printf('<tr class="list_item"><td class="blank">&nbsp;</td><td class="heading">dn</td><td class="value">%s</td></tr>',htmlspecialchars(dn_unescape($dn)));
	}

	# Iterate over each attribute for this entry
	foreach ($dndetails as $attr => $values) {
		# Ignore DN, we've already displayed it.
		if ($attr == 'dn')
			continue;

		if ($ldapserver->isAttrBinary($attr))
			$values = array('(binary)');

		echo '<tr class="list_item">';
		echo '<td class="blank">&nbsp;</td>';
		printf('<td class="heading" valign="top">%s</td>',$_SESSION[APPCONFIG]->getFriendlyHTML($attr));

		echo '<td class="value">';

		if ($ldapserver->isJpegPhoto($attr))
			draw_jpeg_photos($ldapserver,$dn,$attr,false,false,'align="left"');

		else
			if (is_array($values))
				foreach ($values as $value)
					echo str_replace(' ','&nbsp;',htmlspecialchars($value)).'<br />'; 

			else
				echo str_replace(' ','&nbsp;',htmlspecialchars($values)).'<br />';

		echo '</td>';
		echo '</tr>';
	}

	echo '</table>';
	echo '<br />';
}
?>
