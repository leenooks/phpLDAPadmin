<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/copy_form.php,v 1.24 2005/07/22 05:47:43 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in via GET variables
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

$dn = $_GET['dn'] ;

$encoded_dn = rawurlencode( $dn );
$rdn = get_rdn( $dn );
$container = get_container( $dn );

$attrs = get_object_attrs( $ldapserver, $dn );
$select_server_html = server_select_list($ldapserver->server_id,true,'dest_server_id');
$children = get_container_contents( $ldapserver, $dn );

include './header.php';

// Draw some javaScrpt to enable/disable the filter field if this may be a recursive copy
if( is_array( $children ) && count( $children ) > 0 ) { ?>

	<script language="javascript">
	//<!--
	function toggle_disable_filter_field( recursive_checkbox )
	{
		if( recursive_checkbox.checked ) {
			recursive_checkbox.form.remove.disabled = false;
			recursive_checkbox.form.filter.disabled = false;
		} else {
			recursive_checkbox.form.remove.disabled = true;
			recursive_checkbox.form.remove.checked = false;
			recursive_checkbox.form.filter.disabled = true;
		}
	}
	//-->
	</script>

<?php } ?>

<body>

<h3 class="title"><?php echo $lang['copyf_title_copy'] . $rdn; ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']?>: <b><?php echo $dn; ?></b></h3>

<center>
<?php echo $lang['copyf_title_copy'] ?><b><?php echo htmlspecialchars( $rdn ); ?></b> <?php echo $lang['copyf_to_new_object']?>:<br />
<br />

<form action="copy.php" method="post" name="copy_form">
<input type="hidden" name="old_dn" value="<?php echo $dn; ?>" />
<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />

<table style="border-spacing: 10px">
<tr>
	<td><acronym title="<?php echo $lang['copyf_dest_dn_tooltip']; ?>"><?php echo $lang['copyf_dest_dn']?></acronym>:</td>
	<td>
		<input type="text" name="new_dn" size="45" value="<?php echo htmlspecialchars( $dn ); ?>" />
		<?php draw_chooser_link( 'copy_form.new_dn', 'true', $rdn ); ?></td>
	</td>
</tr>

<tr>
	<td><?php echo $lang['copyf_dest_server']?>:</td>
	<td><?php echo $select_server_html; ?></td>
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
<tr>
	<td><?php echo $lang['delete_after_copy']; ?></td>
	<td><input type="checkbox" name="remove" value="yes"/ disabled>
	<small>(<?php echo $lang['delete_after_copy_warn']; ?>)</small)</td>
</tr>
<?php } else { ?>
<tr>
	<td><?php echo $lang['delete_after_copy']; ?></td>
	<td><input type="checkbox" name="remove" value="yes"/></td>
</tr>
<?php } ?>
<tr>
	<td colspan="2" align="right"><input type="submit" value="<?php echo $lang['copyf_title_copy']; ?>" /></td>
</tr>
</table>
</form>

<script language="javascript">
//<!--
/* If the user uses the back button, this way we draw the filter field properly. */
toggle_disable_filter_field( document.copy_form.recursive );
//-->
</script>

<?php if ($config->GetValue('appearance','show_hints')) {?>
	<small><img src="images/light.png" /><span class="hint"><?php echo $lang['copyf_note']?></span></small>
<?php } ?>

</center>
</body>
</html>
