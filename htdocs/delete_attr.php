<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/delete_attr.php,v 1.13 2005/09/17 20:04:29 wurley Exp $
 
/**
 *  Deletes an attribute from an entry with NO confirmation.
 *
 * Variables that come in via common.php
 * - server_id
 *
 *  On success, redirect to edit.php
 *  On failure, echo an error.
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

$dn = isset($_POST['dn']) ? $_POST['dn'] : null;
$attr = isset($_POST['attr']) ? $_POST['attr'] : null;

if (! $dn)
	pla_error($lang['no_dn_specified']);

if (! $attr)
	pla_error($lang['no_attr_specified']);

$encoded_dn = rawurlencode($dn);

if (is_attr_read_only($ldapserver,$attr))
	pla_error(sprintf($lang['attr_is_read_only'],htmlspecialchars($attr)));

$update_array = array();
$update_array[$attr] = array();

$res = @ldap_modify($ldapserver->connect(),$dn,$update_array);
if ($res) {
	$redirect_url = sprintf("edit.php?server_id=%s&dn=%s",$ldapserver->server_id,$encoded_dn);

	foreach($update_array as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	header("Location: $redirect_url");

} else {
	pla_error($lang['could_not_perform_ldap_modify'],ldap_error($ldapserver->connect()),ldap_errno($ldapserver->connect()));
}
?>
