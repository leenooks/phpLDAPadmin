<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rename_form.php,v 1.8 2005/07/22 05:51:57 wurley Exp $
 
/**
 * Displays a form for renaming an LDAP entry.
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
$rdn = get_rdn( $dn );

include './header.php'; ?>

<body>

<h3 class="title"><?php echo sprintf( $lang['rename_entry'], htmlspecialchars( $rdn ) ); ?></b></h3>
<h3 class="subtitle"><?php echo $lang['server']; ?>: <b><?php echo $ldapserver->name; ?></b> &nbsp;&nbsp;&nbsp; <?php echo $lang['distinguished_name']; ?>: <b><?php echo htmlspecialchars( ( $dn ) ); ?></b></h3>

<br />
<center>
<form action="rename.php" method="post" class="edit_dn" />
<input type="hidden" name="server_id" value="<?php echo $ldapserver->server_id; ?>" />
<input type="hidden" name="dn" value="<?php echo $dn; ?>" />
<input type="text" name="new_rdn" size="30" value="<?php echo htmlspecialchars( ( $rdn ) ); ?>" />
<input class="update_dn" type="submit" value="<?php echo $lang['rename']; ?>" />
</form>
</center>
</body>
</html>
