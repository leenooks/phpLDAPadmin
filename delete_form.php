<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/delete_form.php,v 1.12 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * delete_form.php
 * Displays a last chance confirmation form to delete a dn.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = pla_explode_dn( $dn );
$rdn = $rdn[0];
$server_name = $servers[$server_id]['name'];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

$children = get_container_contents( $server_id, $dn );
$has_children = count($children)>0 ? true : false;

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( $lang['delete_dn'], htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<?php  if( $has_children ) { ?>

<center><b><?php echo $lang['permanently_delete_children']; ?></b><br /><br />

<?php
flush(); // so the user can get something on their screen while we figure out how many children this object has
if( $has_children ) {
	// get the total number of child objects (whole sub-tree)
	$s = pla_ldap_search( $server_id, 'objectClass=*', $dn, array('dn'), 'sub' );
	$sub_tree_count = count( $s );
}
?>

<table class="delete_confirm">
<td>

<p>
<?php echo sprintf( $lang['entry_is_root_sub_tree'], $sub_tree_count ); ?> 
<small>(<a href="search.php?search=true&amp;server_id=<?php echo $server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=sub"><?php echo $lang['view_entries']; ?></a>)</small>
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
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" class="scary" value="<?php echo sprintf( $lang['delete_all_x_objects'], $sub_tree_count ); ?>" />
	</form>
	</td>
	
	<td>
	<center>
	<form action="edit.php" method="get">
	<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="<?php echo $lang['cancel']; ?>" class="cancel" />
	</form>
	</center>
	</td>
</tr>
</table>
</td>
</table>
<?php flush(); ?>
<br />
<br />
<?php echo $lang['list_of_entries_to_be_deleted']; ?><br />
<select size="<?php echo min( 10, $sub_tree_count );?>" multiple disabled style="background:white; color:black;width:500px" >
<?php $i=0; ?>
<?php foreach( $s as $dn => $junk ) { ?>
	<?php $i++; ?>
	<option><?php echo $i; ?>. <?php echo htmlspecialchars( ( $dn ) ); ?></option>
<?php } ?>

</select>

<br />

<?php  } else { ?>

<center>

<table class="delete_confirm">
<td>

<?php echo $lang['sure_permanent_delete_object']; ?><br />
<br />
<nobr><acronym title="<?php echo $lang['distinguished_name']; ?>"><?php echo $lang['dn']; ?></acronym>:  <b><?php echo pretty_print_dn( $dn ); ?></b><nobr><br />
<nobr><?php echo $lang['server']; ?>: <b><?php echo htmlspecialchars($server_name); ?></b></nobr><br />
<br />
<table width="100%">
<tr>
	<td>
	<center>
	<form action="delete.php" method="post">
	<input type="hidden" name="dn" value="<?php echo htmlspecialchars($dn); ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="<?php echo $lang['delete']; ?>" class="scary" />
	</center>
	</form>
	</td>
	
	<td>
	<center>
	<form action="edit.php" method="get">
	<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="<?php echo $lang['cancel']; ?>" class="cancel" />
	</form>
	</center>
	</td>
</tr>
</table>

</td>
</table>

</center>

<?php  } ?>

</body>

</html>
