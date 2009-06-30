<?php 

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
	pla_error( "You cannot perform updates while server is in read-only mode" );

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$children = get_container_contents( $server_id, $dn );
$has_children = count($children)>0 ? true : false;

?>

<?php include 'header.php'; ?>
<body>

<h3 class="title">Delete <b><?php echo htmlspecialchars( utf8_decode( $rdn ) ); ?></b></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo htmlspecialchars( utf8_decode( $dn ) ); ?></b></h3>

<?php if( 0 == strcasecmp( $dn, $servers[$server_id]['base'] ) ) { ?>

	<center><b>You cannot delete the base <acronym title="Distinguished Name">DN</acronym> entry of the LDAP server.</b></center>
	</body>
	</html>
	<?php exit; ?>

<?php } ?>


<?php  if( $has_children ) { ?>

<center><b>Permanently delete all children also?</b><br /><br />

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

<p>This object is the root of a sub-tree containing <a href="search.php?search=true&amp;server_id=<?php echo $server_id; ?>&amp;filter=<?php echo rawurlencode('objectClass=*'); ?>&amp;base_dn=<?php echo $encoded_dn; ?>&amp;form=advanced&amp;scope=sub"><?php echo ($sub_tree_count); ?> objects</a>

phpLDAPadmin can recursively delete this object and all <?php echo ($sub_tree_count-1); ?> of its children. See below for a list of DNs
that this will delete. Do you want to do this?<br />
<br />
<small>Note: This is potentially very dangerous and you do this at your own risk. This operation cannot be undone.
Take into consideration aliases and other such things that may cause problems.</small>
<br />
<br />
<table width="100%">
<tr>
	<td>
	<center>
	<form action="rdelete.php" method="post">
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" class="scary" value="Delete all <?php echo ($sub_tree_count); ?> objects" />
	</form>
	</td>
	
	<td>
	<center>
	<form action="edit.php" method="get">
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="Cancel" class="cancel" />
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
A list of all the <?php echo ($sub_tree_count); ?> <acronym title="Distinguished Name">DN</acronym>s that this action will delete:<br />
<select size="<?php echo min( 10, $sub_tree_count );?>" multiple disabled style="background:white; color:black;width:500px" >
<?php $i=0; ?>
<?php foreach( $s as $dn => $junk ) { ?>
	<?php $i++; ?>
	<option><?php echo $i; ?>. <?php echo htmlspecialchars( utf8_decode( $dn ) ); ?></option>
<?php } ?>

</select>

<br />

<?php  } else { ?>

<center>

<table class="delete_confirm">
<td>

Are you sure you want to permanently delete this object?<br />
<br />
<nobr><acronym title="Distinguished Name">DN</acronym>:  <b><?php echo htmlspecialchars(utf8_decode($dn)); ?></b><nobr><br />
<nobr>Server: <b><?php echo htmlspecialchars($server_name); ?></b></nobr><br />
<br />
<table width="100%">
<tr>
	<td>
	<center>
	<form action="delete.php" method="post">
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="Delete It" class="scary" />
	</center>
	</form>
	</td>
	
	<td>
	<center>
	<form action="edit.php" method="get">
	<input type="hidden" name="dn" value="<?php echo $encoded_dn; ?>" />
	<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
	<input type="submit" name="submit" value="Cancel" class="cancel" />
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


