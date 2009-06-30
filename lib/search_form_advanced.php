<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_advanced.php,v 1.26.2.1 2007/12/26 09:26:33 wurley Exp $

/**
 * @package phpLDAPadmin
 */

?>

<script type="text/javascript" language="javascript">

<?php
foreach ($server_info_list as $i => $ignore) {
	foreach ($server_info_list[$i]['base_dns'] as $base_dn) { ?>
		addToServersList(new server(<?php echo $i; ?>,"<?php echo $server_info_list[$i]['name']; ?>","<?php echo $base_dn; ?>"));
<?php
	}
} ?>

function focus_filter() {
	document.advanced_search_form.filter.focus();
}
</script>

<form action="cmd.php" method="get" class="search" name="advanced_search_form">
<input type="hidden" name="cmd" value="search" />
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="advanced" />
<input type="hidden" name="format" value="<?php echo $entry['format']; ?>" />

<table class="search" border=0>
	<tr><td class="title" colspan=2><?php echo _('Advanced Search Form'); ?></td></tr>

<?php
	$ss = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'simple_search');
	$ps = $_SESSION[APPCONFIG]->isCommandAvailable('search', 'predefined_search');
	if ($ss | $ps) {
		echo '<tr><td class="subtitle" colspan=2>(';
		if ($ss) {
			echo '<a href="cmd.php?cmd=search&amp;server_id=';
			echo $ldapserver->server_id;
			echo '&amp;form=simple">';
			echo _('Simple Search Form');
			echo '</a>';
			if ($ps) echo '	| ';
		}
		if ($ps) {
			echo '<a href="cmd.php?cmd=search&amp;server_id=';
			echo $ldapserver->server_id;
			echo '&amp;form=predefined">';
			echo _('Predefined Searches');
			echo '</a>';
		}
		echo ')</td></tr>';
	}
?>

	<tr><td colspan=2>&nbsp;</td></tr>

	<tr><td><small><?php echo _('Server'); ?></small></td><td><?php echo $server_menu_html; ?></td></tr>

	<tr>
		<td><small><?php echo _('Base DN'); ?></small></td>
		<td><input type="text" name="base_dn" value="<?php echo count($base_dns) == 1 ? $base_dns[0] : '' ?>" style="width: 200px" id="base_dn" />

<?php
draw_chooser_link('advanced_search_form.base_dn');

if (isset($entry['base_dn']['invalid']) && $entry['base_dn']['invalid'])
	printf('<small style="color:red; white-space: nowrap">%s</small>',_('This is not a valid DN.'));

if (isset($entry['base_dn']['exist']) && $entry['base_dn']['exist'])
	printf('<small style="color:red; white-space: nowrap">%s</small>',_('This entry does not exist.'));
?>
		</td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo _('The scope in which to search'); ?>"><?php echo _('Search Scope'); ?></acronym></small></td>
		<td>
			<select name="scope" style="width: 200px">
				<option<?php echo $entry['scope']=='sub'?' selected':''; ?> value="sub">
					<?php echo _('Sub (entire subtree)'); ?>
				</option>
				<option<?php echo $entry['scope']=='one'?' selected':''; ?> value="one">
					<?php echo _('One (one level beneath base)'); ?>
				</option>
				<option<?php echo $entry['scope']=='base'?' selected':''; ?> value="base">
					<?php echo _('Base (base dn only)'); ?>
				</option>
			</select>
		</td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo htmlspecialchars(_('Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))')); ?>">
			<?php echo _('Search Filter'); ?></acronym></small></td>
		<td><input type="text" name="filter" id="filter" style="width: 200px" value="<?php echo $entry['filter']['clean'] ? htmlspecialchars($entry['filter']['clean']) : 'objectClass=*'; ?>" /></td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo _('A list of attributes to display in the results (comma-separated)'); ?>">
			<?php echo _('Show Attributtes'); ?></acronym></small></td>

		 <td><input type="text" name="display_attrs" style="width: 200px" value="<?php
			echo $entry['display']['string'] ? htmlspecialchars($entry['display']['string']) :
					join(', ',$_SESSION[APPCONFIG]->GetValue('search','result_attributes')); ?>" /></td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo htmlspecialchars(_('Order by').'...'); ?>"><?php echo _('Order by'); ?></acronym></small></td>
		<td><input type="text" name="orderby" id="orderby" style="width: 200px" value="<?php echo htmlspecialchars($entry['orderby']['string']) ?>" /></td>
	</tr>

	<tr><td colspan="2"><br /><center><input type="submit" value="<?php echo _('Search'); ?>" /></center></td></tr>
</table>
</form>

<script type="text/javascript" language="javascript">
	// Move the cursor to the filter field
	focus_filter();
</script>
