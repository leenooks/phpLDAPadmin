<?php

/*
 * copy_form.php
 * Copies a given object to create a new one.
 *
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$dn = $_GET['dn'] ;
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = pla_explode_dn( $dn );
$container = $rdn[ 1 ];
for( $i=2; $i<count($rdn)-1; $i++ )
	$container .= ',' . $rdn[$i];
$rdn = $rdn[0];

check_server_id( $server_id ) or pla_error( $lang['bad_server_id_underline'] . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

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

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['copyf_title_copy'] . $rdn; ?></h3>
<h3 class="subtitle">Server: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']?>: <b><?php echo $dn; ?></b></h3>

<center>
<?php echo $lang['copyf_title_copy'] ?><b><?php echo htmlspecialchars( $rdn ); ?></b> <?php echo $lang['copyf_to_new_object']?>:<br />
<br />
<form action="copy.php" method="post" name="copy_form">
<input type="hidden" name="old_dn" value="<?php echo $dn; ?>" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

<table>
<tr>
	<td><acronym title="<?php echo $lang['copyf_dest_dn_tooltip']; ?>"><?php echo $lang['copyf_dest_dn']?></acronym>:</td>
	<td>
		<input type="text" name="new_dn" size="45" value="<?php echo htmlspecialchars( $dn ); ?>" />
		<?php draw_chooser_link( 'copy_form.new_dn' ); ?></td>
	</td>
</tr>

<tr>
	<td><?php echo $lang['copyf_dest_server']?>:</td>
	<td><select name="dest_server_id"><?php echo $select_server_html; ?></select></td>
</tr>
<?php if( show_hints() ) {?>
<tr>
	<td colspan="2"><small><img src="images/light.png" /><?php echo $lang['copyf_note']?></small></td>
</tr>
<?php } ?>

<?php if( is_array( $children ) && count( $children ) > 0 ) { ?>
<tr>
	<td colspan="2"><input type="checkbox" name="recursive" />
	<?php echo $lang['copyf_recursive_copy']?></td>
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
