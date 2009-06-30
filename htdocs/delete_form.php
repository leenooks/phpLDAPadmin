<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_form.php,v 1.20 2005/07/22 05:47:44 wurley Exp $

/**
 * delete_form.php
 * Displays a last chance confirmation form to delete a dn.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error($lang['no_updates_in_read_only_mode']);
if (! $ldapserver->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$dn = $_GET['dn'];

$encoded_dn = rawurlencode( $dn );
$rdn = pla_explode_dn( $dn );
$rdn = $rdn[0];
$children = get_container_contents( $ldapserver,$dn,0,'(objectClass=*)',LDAP_DEREF_NEVER );
$has_children = count($children) > 0 ? true : false;

include './header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( $lang['delete_dn'], htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<?php if( $has_children ) { ?>

<center><b><?php echo $lang['permanently_delete_children']; ?></b><br /><br />

<?php
	flush();

	# get the total number of child objects (whole sub-tree)
	$s = pla_ldap_search( $ldapserver, 'objectClass=*', $dn, array('dn'), 'sub' );
	$sub_tree_count = count( $s );
?>

<table class="delete_confirm">
<tr>
	<td>
	<p>
	<?php echo sprintf( $lang['entry_is_root_sub_tree'], $sub_tree_count ); ?>
	<small>(<a href="search.php?search=true&amp;server_id=<?php echo $ldapserver->server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=sub"><?php echo $lang['view_entries']; ?></a>)</small>
	<br />
	<br />

	<?php echo sprintf( $lang['confirm_recursive_delete'], ($sub_tree_count-1) ); ?><br />
	<br />
	<small><?php echo $lang['confirm_recursive_delete_note']; ?></small>

	<br />
	<br />
	<table width="100%">
	<tr>
		<td>
		<center>
			<form action="rdelete.php" method="post">
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" class="scary" value="<?php echo sprintf( $lang['delete_all_x_objects'], $sub_tree_count ); ?>" />
			</form>
		</center>
		</td>

		<td>
		<center>
			<form action="edit.php" method="get">
			<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo $lang['cancel']; ?>" class="cancel" />
			</form>
		</center>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<?php flush(); ?>
<br />
<br />
<?php echo $lang['list_of_entries_to_be_deleted']; ?><br />

<select size="<?php echo min( 10, $sub_tree_count );?>" multiple disabled style="background:white; color:black;width:500px" >
	<?php $i=0;
	foreach( $s as $dn => $junk ) {
		$i++; ?>

	<option><?php echo $i; ?>. <?php echo htmlspecialchars( ( $dn ) ); ?></option>
	<?php } ?>
</select>

<br />

<?php } else { ?>

<center>
<table class="delete_confirm">
<tr>
	<td>
	<?php echo $lang['sure_permanent_delete_object']; ?><br />
	<br />
	<nobr><acronym title="<?php echo $lang['distinguished_name']; ?>"><?php echo $lang['dn']; ?></acronym>:  <b><?php echo pretty_print_dn( $dn ); ?></b><nobr><br />
	<nobr><?php echo $lang['server']; ?>: <b><?php echo htmlspecialchars($ldapserver->name); ?></b></nobr><br />
	<br />

	<table width="100%">
	<tr>
		<td>
			<center>
			<form action="delete.php" method="post">
			<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo $lang['delete']; ?>" class="scary" />
			</form>
			</center>
		</td>

		<td>
			<center>
			<form action="edit.php" method="get">
			<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
			<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
			<input type="submit" name="submit" value="<?php echo $lang['cancel']; ?>" class="cancel" />
			</form>
			</center>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</center>

<?php } ?>

</body>
</html>
