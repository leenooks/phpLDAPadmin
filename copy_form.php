<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/copy_form.php,v 1.16 2004/03/19 20:13:08 i18phpldapadmin Exp $


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
$rdn = get_rdn( $dn );
$container = get_container( $dn );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id_underline'] . htmlspecialchars( $server_id ) );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

$attrs = get_object_attrs( $server_id, $dn );
$server_name = $servers[$server_id]['name'];

$select_server_html = "";
foreach( $servers as $id => $server )
	if( $server['host'] )
		$select_server_html .= "<option value=\"$id\"". ($id==$server_id?" selected":"") .">" . htmlspecialchars($server['name']) . "</option>\n";

$children = get_container_contents( $server_id, $dn );

include 'header.php'; 

// Draw some javaScrpt to enable/disable the filter field if this may be a recursive copy
if( is_array( $children ) && count( $children ) > 0 ) { ?>
<script language="javascript">
//<!--
	function toggle_disable_filter_field( recursive_checkbox )
	{
		if( recursive_checkbox.checked ) {
			recursive_checkbox.form.filter.disabled = false;
		} else {
			recursive_checkbox.form.filter.disabled = true;
		}
	}
//-->
</script>
<?php } ?>

<body>

<h3 class="title"><?php echo $lang['copyf_title_copy'] . $rdn; ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']?>: <b><?php echo $dn; ?></b></h3>

<center>
<?php echo $lang['copyf_title_copy'] ?><b><?php echo htmlspecialchars( $rdn ); ?></b> <?php echo $lang['copyf_to_new_object']?>:<br />
<br />
<form action="copy.php" method="post" name="copy_form">
<input type="hidden" name="old_dn" value="<?php echo $dn; ?>" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />

<table style="border-spacing: 10px">
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

<?php if( is_array( $children ) && count( $children ) > 0 ) { ?>
<tr>
	<td><label for="recursive"><?php echo $lang['recursive_copy']; ?></label>:</td>
	<td><input type="checkbox" id="recursive" name="recursive" onClick="toggle_disable_filter_field(this)" />
	<small>(<?php echo $lang['copyf_recursive_copy']?>)</small></td>
</tr>
<tr>
	<td><acronym title="<?php echo $lang['filter_tooltip']; ?>"><?php echo $lang['filter']; ?></acronym>:</td>
	<td><input type="text" name="filter" value="(objectClass=*)" size="45" disabled />
</tr>
<?php } ?>

<tr>
	<td colspan="2" align="right"><input type="submit" value="<?php echo $lang['copyf_title_copy']; ?>" /></td>
</tr>
</table>
</form>

<script language="javascript">
    //<!--
    /* If the user uses the back button, this way we draw the filter field
       properly. */
    toggle_disable_filter_field( document.copy_form.recursive );
    //-->
</script>

<?php if( show_hints() ) {?>
	<small><img src="images/light.png" /><span class="hint"><?php echo $lang['copyf_note']?></span></small>
<?php } ?>

</center>
</body>
</html>
