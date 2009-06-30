<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_results_list.php,v 1.5.4.4 2005/12/09 14:31:27 wurley Exp $

/**
 * @package phpLDAPadmin
 */

$friendly_attrs = process_friendly_attr_table();

# Iterate over each entry
$i = 0;

foreach ($results as $dn => $dndetails) {
	$i++;

	if ($i <= $start_entry)
		continue;

	if ($i >= $end_entry)
		break;

	echo '<div class="search_result">';
	echo '<table><tr>';
	printf('<td><img src="images/%s" /></td>',get_icon($ldapserver,$dn));
	printf('<td><a href="template_engine.php?server_id=%s&amp;dn=%s">%s</a></td>',
		$ldapserver->server_id,rawurlencode(dn_unescape($dn)),htmlspecialchars(get_rdn($dn)));
	echo '</tr></table>';
	echo '</div>';

	echo '<table class="attrs">';
	printf('<tr><td class="attr" valign="top">dn</td><td>%s</td></tr>',htmlspecialchars(dn_unescape($dn)));

	# Iterate over each attribute for this entry
	foreach ($dndetails as $attr => $values) {
		# Ignore DN, we've already displayed it.
		if ($attr == 'dn')
			continue;

		if ($ldapserver->isAttrBinary($attr))
			$values = array('(binary)');

		if (isset($friendly_attrs[strtolower($attr)]))
			$attr = sprintf('<acronym title="Alias for $attr">%s</acronym>',
				htmlspecialchars($friendly_attrs[strtolower($attr)]));
		else
			$attr = htmlspecialchars($attr);

		echo '<tr>';
		printf('<td class="attr" valign="top">%s</td>',$attr);
		echo '<td class="val">';

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

	# Flush every 5th entry (speeds things up a bit)
	if ($i % 5 == 0)
		flush();
}
?>
