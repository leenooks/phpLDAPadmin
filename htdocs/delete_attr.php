<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_attr.php,v 1.14.2.5 2005/12/09 14:29:37 wurley Exp $

/**
 *  Deletes an attribute from an entry with NO confirmation.
 *
 * Variables that come in via common.php
 * - server_id
 *
 *  On success, redirect to template_engine.php
 *  On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = isset($_POST['dn']) ? $_POST['dn'] : null;
$attr = isset($_POST['attr']) ? $_POST['attr'] : null;

if (! $dn)
	pla_error(_('No DN specified'));

if (! $attr)
	pla_error(_('No attribute name specified.'));

$encoded_dn = rawurlencode($dn);

if ($ldapserver->isAttrReadOnly($attr))
	pla_error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),htmlspecialchars($attr)));

$update_array = array();
$update_array[$attr] = array();

$res = $ldapserver->modify($dn,$update_array);
if ($res) {
	$redirect_url = sprintf('template_engine.php?server_id=%s&dn=%s',$ldapserver->server_id,$encoded_dn);

	foreach($update_array as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	header("Location: $redirect_url");

} else {
	pla_error(_('Could not perform ldap_modify operation.'),$ldapserver->error(),$ldapserver->errno());
}
?>
