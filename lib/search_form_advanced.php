<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/lib/search_form_advanced.php,v 1.23.2.3 2006/10/28 05:56:56 wurley Exp $

/**
 * @package phpLDAPadmin
 */

?>

<script type="text/javascript" language="javascript">

<?php foreach ($server_info_list as $i => $ignore) {
           foreach ($server_info_list[$i]['base_dns'] as $base_dn) { ?>

addToServersList(new server(<?php echo $i; ?>,"<?php echo $server_info_list[$i]['name']; ?>","<?php echo $base_dn; ?>"));

<?php } } ?>

function focus_filter() {
    document.advanced_search_form.filter.focus();
}
</script>

<form action="search.php" method="get" class="search" name="advanced_search_form">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="advanced" />
<input type="hidden" name="format" value="<?php echo $format; ?>" />

<center><b><?php echo _('Advanced Search Form'); ?></b></center>
<small>(<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=simple"><?php echo _('Simple Search Form'); ?></a> |
	<a href="search.php?server_id=<?php echo $ldapserver->server_id; ?>&amp;form=predefined"><?php echo _('Predefined Searches'); ?></a>)</small><br />
<br />

<table>
	<tr>
		<td><small><?php echo _('Server'); ?></small></td>
		<td><?php echo $server_menu_html; ?></td>
	</tr>

	<tr>
		<td><small><?php echo _('Base DN'); ?></small></td>
		<td><input type="text" name="base_dn" value="<?php echo count($base_dns) == 1 ? $base_dns[0] : '' ?>" style="width: 200px" id="base_dn" />

<?php draw_chooser_link( 'advanced_search_form.base_dn' );

if( isset( $base_dn_is_invalid ) && $base_dn_is_invalid  )
	echo "<small style=\"color:red; white-space: nowrap\">" . _('This is not a valid DN.') . "</small>";

if( isset( $base_dn_does_not_exist ) && $base_dn_does_not_exist )
	echo "<small style=\"color:red; white-space: nowrap\">" . _('This entry does not exist.') . "</small>"; ?>

	        </td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo _('The scope in which to search'); ?>"><?php echo _('Search Scope'); ?></acronym></small></td>
		<td>
			<select name="scope" style="width: 200px">
				<option<?php echo $scope=='sub'?' selected':''; ?> value="sub">
					<?php echo _('Sub (entire subtree)'); ?>
				</option>
				<option<?php echo $scope=='one'?' selected':''; ?> value="one">
					<?php echo _('One (one level beneath base)'); ?>
				</option>
				<option<?php echo $scope=='base'?' selected':''; ?> value="base">
					<?php echo _('Base (base dn only)'); ?>
				</option>
			</select>
		</td>

	</tr>

	<tr>
		<td><small><acronym title="<?php echo htmlspecialchars(_('Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))')); ?>">
			<?php echo _('Search Filter'); ?></acronym></small></td>

		<td><input type="text" name="filter" id="filter" style="width: 200px" value="<?php echo  $filter ? htmlspecialchars($filter) : 'objectClass=*'; ?>" /></td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo _('A list of attributes to display in the results (comma-separated)'); ?>">
			<?php echo _('Show Attributtes'); ?></acronym></small></td>

		<td><input type="text" name="display_attrs" style="width: 200px" value="<?php
			echo isset( $_GET['display_attrs'] ) ?
					htmlspecialchars( $_GET['display_attrs'] ) :
					join(', ',$config->GetValue('search','result_attributes')); ?>" />

	</tr>
	<tr>
		<td><small><acronym title="<?php echo htmlspecialchars(_('Order by').'...'); ?>">
			<?php echo _('Order by'); ?></acronym></small></td>

		<td><input type="text" name="orderby" id="orderby" style="width: 200px" value="<?php echo  $filter ? htmlspecialchars($orderby) : ''; ?>" /></td>
	</tr>
	<tr>
		<td colspan="2"><br /><center><input type="submit" value="<?php echo _('Search'); ?>" /></center></td>
	</tr>
</table>
</form>

<script type="text/javascript" language="javascript">
    // Move the cursor to the filter field
    focus_filter();
</script>
