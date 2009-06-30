<script>
<?
$num_server = count($server_info_list);
for($i=0;$i<$num_server;$i++){
?>
 addToServersList(new server(<?=$i?>,"<?=$server_info_list[$i]['name']?>","<?=$server_info_list[$i]['base_dn']?>"));
<? 
}
?>
</script>

<form action="search.php" method="get" class="search">
<input type="hidden" name="search" value="true" />
<input type="hidden" name="form" value="advanced" />

<center><b><?php echo $lang['advanced_search_form_str']; ?></b></center>
<small>(<a href="search.php?server_id=<?php echo $server_id; ?>&amp;form=simple"><?php echo $lang['simple_search_form_str']; ?></a>)</small><br />
<br />

<table>
	<tr>
		<td><small>Server</small></td>
		<td><?php echo $server_menu_html; ?></td>
	</tr>

	<tr>
		<td><small>Base <acronym title="Distinguished Name">DN</acronym></small></td>
		<td><input type="text" name="base_dn" value="<?php echo htmlspecialchars($base_dn); ?>" size="30" id="base_dn" /></td>
	</tr>

	<tr>
		<td><small><acronym title="The scope in which to search">Search Scope</acronym></small></td>
		<td>
			<select name="scope">
			<option<?php echo $scope=='sub'?' selected':''; ?> value="sub">Sub (entire subtree)</option>
			<option<?php echo $scope=='one'?' selected':''; ?> value="one">One (one level beneath base)</option>
			<option<?php echo $scope=='base'?' selected':''; ?> value="base">Base (base dn only)</option>
			</select>
		</td>

	</tr>

	<tr>
		<td><small><acronym title="Standard LDAP search filter. Example: (&(sn=Smith)(givenname=David))">
			Search Filter</acronym></small></td>
		<td><input type="text" name="filter" size="30" value="<?php echo  $filter ? htmlspecialchars(utf8_decode($filter)) : 'objectClass=*'; ?>" /></td>
	</tr>

	<tr>
		<td><small><acronym title="A list of attributes to display in the results (comma-separated)">
			Show Attributes</acronym></small></td>
		<td><input type="text" name="display_attrs" size="30" value="<?php
			echo isset( $_GET['display_attrs'] ) ? $_GET['display_attrs'] : $search_result_attributes; ?>" />

	<tr>
		<td colspan="2"><br /><center><input type="submit" value="Search" /></center></td>
	</tr>
</table>
</form>


