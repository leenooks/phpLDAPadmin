<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_attr.php,v 1.16 2007/12/15 07:50:30 wurley Exp $

/**
 *  Deletes an attribute from an entry with NO confirmation.
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

if (! $_SESSION['plaConfig']->isCommandAvailable('attribute_delete'))
	pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete attribute')));

$entry['dn']['string'] = get_request('dn');
$entry['dn']['encode'] = rawurlencode($entry['dn']['string']);
$entry['attr'] = get_request('attr');

if (! $entry['dn']['string'])
	pla_error(_('No DN specified'));

if (! $entry['attr'])
	pla_error(_('No attribute name specified.'));

if ($ldapserver->isAttrReadOnly($entry['attr']))
	pla_error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),htmlspecialchars($entry['attr'])));

$update_array = array();
$update_array[$entry['attr']] = array();

$result = $ldapserver->modify($entry['dn']['string'],$update_array);
if ($result) {
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,$entry['dn']['encode']);

	foreach ($update_array as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	header("Location: $redirect_url");
	die();

} else {
	pla_error(_('Could not perform ldap_modify operation.'),$ldapserver->error(),$ldapserver->errno());
}
?>
