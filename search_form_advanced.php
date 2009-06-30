<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_form_advanced.php,v 1.21 2005/03/05 09:41:01 wurley Exp $

/**
 * @package phpLDAPadmin
 */

?>

<script>

<?php foreach ($server_info_list as $i => $ignore) { ?>

addToServersList(new server(<?php echo $i; ?>,"<?php echo $server_info_list[$i]['name']; ?>","<?php echo $server_info_list[$i]['base_dn']; ?>"));

<?php } ?>

function focus_filter() {
    document.advanced_search_form.filter.focus();
}
</script>

<form action="search.php" method="get" class="search" name="advanced_search_form">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="advanced" />
<input type="hidden" name="format" value="<?php echo $format; ?>" />

<center><b><?php echo $lang['advanced_search_form_str']; ?></b></center>
<small>(<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=simple"><?php echo $lang['simple_search_form_str']; ?></a> |
	<a href="search.php?form=predefined"><?php echo $lang['predefined_searches']; ?></a>)</small><br />
<br />

<table>
	<tr>
		<td><small><?php echo $lang['server']; ?></small></td>
		<td><?php echo $server_menu_html; ?></td>
	</tr>

	<tr>
		<td><small><?php echo $lang['base_dn']; ?></small></td>
		<td><input type="text" name="base_dn" value="<?php echo count($base_dns) == 1 ? $base_dns[0] : '' ?>" style="width: 200px" id="base_dn" />

<?php draw_chooser_link( 'advanced_search_form.base_dn' );

if( isset( $base_dn_is_invalid ) && $base_dn_is_invalid  )
	echo "<small style=\"color:red; white-space: nowrap\">" . $lang['this_base_dn_is_not_valid'] . "</small>";

if( isset( $base_dn_does_not_exist ) && $base_dn_does_not_exist )
	echo "<small style=\"color:red; white-space: nowrap\">" . $lang['this_base_dn_does_not_exist'] . "</small>"; ?>

	        </td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo $lang['scope_in_which_to_search']; ?>"><?php echo $lang['search_scope']; ?></acronym></small></td>
		<td>
			<select name="scope" style="width: 200px">
				<option<?php echo $scope=='sub'?' selected':''; ?> value="sub">
					<?php echo $lang['scope_sub']; ?>
				</option>
				<option<?php echo $scope=='one'?' selected':''; ?> value="one">
					<?php echo $lang['scope_one']; ?>
				</option>
				<option<?php echo $scope=='base'?' selected':''; ?> value="base">
					<?php echo $lang['scope_base']; ?>
				</option>
			</select>
		</td>

	</tr>

	<tr>
		<td><small><acronym title="<?php echo $lang['standard_ldap_search_filter']; ?>">
			<?php echo $lang['search_filter']; ?></acronym></small></td>

		<td><input type="text" name="filter" id="filter" style="width: 200px" value="<?php echo  $filter ? htmlspecialchars($filter) : 'objectClass=*'; ?>" /></td>
	</tr>

	<tr>
		<td><small><acronym title="<?php echo $lang['list_of_attrs_to_display_in_results']; ?>">
			<?php echo $lang['show_attributes']; ?></acronym></small></td>

		<td><input type="text" name="display_attrs" style="width: 200px" value="<?php
			echo isset( $_GET['display_attrs'] ) ?
					htmlspecialchars( $_GET['display_attrs'] ) :
					$search_result_attributes; ?>" />

	</tr>
	<tr>
		<td colspan="2"><br /><center><input type="submit" value="<?php echo $lang['Search']; ?>" /></center></td>
	</tr>
</table>
</form>

<script language="javascript">
    // Move the cursor to the filter field
    focus_filter();
</script>
