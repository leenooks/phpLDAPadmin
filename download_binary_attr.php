<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/download_binary_attr.php,v 1.12 2005/07/22 05:47:44 wurley Exp $

/**
 * @package phpLDAPadmin
 * Variables that come in via common.php
 *  - server_id
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error($lang['no_updates_in_read_only_mode']);
if (! $ldapserver->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$dn = rawurldecode($_GET['dn']);
$attr = $_GET['attr'];

# if there are multiple values in this attribute, which one do you want to see?
$value_num = isset($_GET['value_num']) ? $_GET['value_num'] : 0;

dn_exists($ldapserver,$dn) or
	pla_error(sprintf($lang['no_such_entry'],pretty_print_dn($dn)));

$search = @ldap_read($ldapserver->connect(),$dn,"(objectClass=*)",array($attr),0,0,0,$config->GetValue('deref','view'));
if (! $search)
	pla_error($lang['error_performing_search'],ldap_error($ldapserver->connect()),ldap_errno($ldapserver->connect()));

$entry = ldap_first_entry($ldapserver->connect(),$search);
$attrs = ldap_get_attributes($ldapserver->connect(),$entry);
$attr = ldap_first_attribute($ldapserver->connect(),$entry,$attrs);
$values = ldap_get_values_len($ldapserver->connect(),$entry,$attr);
$count = $values['count'];

// Dump the binary data to the browser
header("Content-type: octet-stream");
header("Content-disposition: attachment; filename=$attr");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
echo $values[$value_num];
?>
