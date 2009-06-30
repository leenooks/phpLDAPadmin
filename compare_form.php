<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/compare_form.php,v 1.2 2005/07/22 05:47:43 wurley Exp $

/**
 * Compares to DN entries side by side.
 *
 *  - dn (rawurlencoded)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if( ! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

$dn = (isset($_GET['dn']) ? $_GET['dn'] : '');

$encoded_dn = rawurlencode( $dn );
$rdn = get_rdn( $dn );
$container = get_container( $dn );

$attrs = get_object_attrs( $ldapserver, $dn );
$select_server_html = server_select_list($ldapserver->server_id,true,'server_id_dst');

include './header.php'; ?>

<body>

<h3 class="title"><?php echo $lang['compare_dn']. '&nbsp;' . $rdn; ?></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b>
<?php if ($dn) { ?>
	 &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']?>: <b><?php echo $dn; ?></b>
<?php } ?>
</h3>

<center>
<?php echo $lang['compare']; ?> <b><?php echo htmlspecialchars( $rdn ); ?></b> <?php echo $lang['with']; ?>:<br />
<br />

<form action="compare.php" method="post" name="compare_form">
<input type="hidden" name="server_id_src" value="<?php echo $ldapserver->server_id; ?>" />

<table style="border-spacing: 10px">
<tr>
	<?php if (! $dn) { ?>
	<td><acronym title="<?php echo $lang['compf_dn_tooltip']; ?>"><?php echo $lang['compf_source_dn']; ?></acronym>:</td>
	<td>
	<input type="text" name="dn_src" size="45" value="<?php echo htmlspecialchars( $dn ); ?>" />
		<?php draw_chooser_link( 'compare_form.dn_src', 'true', $rdn ); ?></td>
	</td>
	<?php } else { ?>
	<input type="hidden" name="dn_src" value="<?php echo htmlspecialchars( $dn ); ?>" />
	<?php } ?>
</tr>
<tr>
	<td><acronym title="<?php echo $lang['compf_dn_tooltip']; ?>"><?php echo $lang['copyf_dest_dn']; ?></acronym>:</td>
	<td>
		<input type="text" name="dn_dst" size="45" value="" />
		<?php draw_chooser_link( 'compare_form.dn_dst', 'true', '' ); ?></td>
	</td>
</tr>

<tr>
	<td><?php echo $lang['copyf_dest_server']?>:</td>
	<td><?php echo $select_server_html; ?></td>
</tr>

<tr>
	<td colspan="2" align="right"><input type="submit" value="<?php echo $lang['compare']; ?>" /></td>
</tr>
</table>
</form>
</center>
</body>
</html>
