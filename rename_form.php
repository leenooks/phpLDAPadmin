<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/rename_form.php,v 1.4 2004/03/19 20:13:08 i18phpldapadmin Exp $
 

/*
 * rename_form.php
 * Displays a form for renaming an LDAP entry.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - server_id
 */

require 'common.php';

$dn = $_GET['dn'];
$encoded_dn = rawurlencode( $dn );
$server_id = $_GET['server_id'];
$rdn = get_rdn( $dn );
$server_name = $servers[$server_id]['name'];

if( is_server_read_only( $server_id ) )
	pla_error( $lang['no_updates_in_read_only_mode'] );

check_server_id( $server_id ) or pla_error( $lang['bad_server_id'] );
have_auth_info( $server_id ) or pla_error( $lang['not_enough_login_info'] );

include 'header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( $lang['rename_entry'], htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $server_name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<br />
<center>
<form action="rename.php" method="post" class="edit_dn" />
<input type="hidden" name="server_id" value="<?php echo $server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
<input type="text" name="new_rdn" size="30" value="<?php echo htmlspecialchars( ( $rdn ) ); ?>" />
<input class="update_dn" type="submit" value="<?php echo $lang['rename']; ?>" />
</form>
</center>
</body>
</html>

