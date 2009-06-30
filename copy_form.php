<?php 

/*
 * copy_form.php
 * Copies a given object to create a new one.
 *
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$dn = rawurldecode( $_GET['dn'] );
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = pla_explode_dn( $dn );
$container = $rdn[ 1 ];
for( $i=2; $i<count($rdn)-1; $i++ )
	$container .= ',' . $rdn[$i];
$rdn = $rdn[0];

check_server_id( $server_id ) or pla_error( "Bad server_id: " . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( "Not enough information to login to server. Please check your configuration." );

$attrs = get_object_attrs( $server_id, $dn );
$server_name = $servers[$server_id]['name'];

$select_server_html = "";
foreach( $servers as $id => $server )
{
	if( $server['host'] )
	{
		$select_server_html .= "<option value=\"$id\"". ($id==$server_id?" selected":"") .">" . $server['name'] . "</option>\n";
	}
}

$children = get_container_contents( $server_id, $dn );

?>

<?php include 'header.php'; ?>
<body>

<h3 class="title">Copy <?php echo utf8_decode( $rdn ); ?></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; Distinguished Name: <b><?php echo $dn; ?></b></h3>

<center>
Copy <b><?php echo htmlspecialchars( utf8_decode( $rdn )); ?></b> to a new object:<br />
<br />
<form action="copy.php" method="post" name="copy_form">
<input type="hidden" name="old_dn" value="<?php echo $encoded_dn; ?>" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

<table>
<tr>
	<td>Destination DN:</td>
	<td>
		<input type="text" name="new_dn" size="45" value="<?php echo htmlspecialchars( utf8_decode( $dn ) ); ?>" />
		<?php draw_chooser_link( 'copy_form.new_dn' ); ?></td>
	</td>
</tr>

<tr>
	<td>Destination Server:</td>
	<td><select name="dest_server_id"><?php echo $select_server_html; ?></select></td>
</tr>
<tr>
	<td colspan="2"><small>Note: Copying between different servers only works if there are no schema violations</small></td>
</tr>
<?php if( is_array( $children ) && count( $children ) > 0 ) { ?>
<tr>
	<td colspan="2"><input type="checkbox" name="recursive" />
	Recursively copy all children of this object as well.</td>
</tr>
<?php } ?>

<tr>
	<td colspan="2" align="right"><input type="submit" value="Copy" /></td>
</tr>
</table>
</form>

</center>
</body>
</html>
