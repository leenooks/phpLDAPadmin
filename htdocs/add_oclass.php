<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/add_oclass.php,v 1.19 2007/12/15 07:50:30 wurley Exp $

/**
 * Adds an objectClass to the specified dn.
 *
 * Note, this does not do any schema violation checking. That is
 * performed in add_oclass_form.php.
 *
 * Variables that come in as POST vars:
 *  - dn (rawurlencoded)
 *  - new_oclass
 *  - new_attrs (array, if any)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

if ($ldapserver->isAttrReadOnly('objectClass'))
	pla_error(_('ObjectClasses are flagged as read only in the phpLDAPadmin configuration.'));

$entry['dn']['encode'] = get_request('dn');
$entry['dn']['string'] = urldecode($entry['dn']['encode']);

$entry['new']['oclass'] = unserialize(rawurldecode(get_request('new_oclass')));
$entry['new']['attrs'] = get_request('new_attrs');

$new_entry = array();
$new_entry['objectClass'] = $entry['new']['oclass'];

if (is_array($entry['new']['attrs']) && count($entry['new']['attrs']) > 0)
	foreach ($entry['new']['attrs'] as $attr => $val) {

		# Check to see if this is a unique Attribute
		if ($badattr = $ldapserver->checkUniqueAttr($entry['dn']['string'],$attr,array($val))) {
			$href['search'] = htmlspecialchars(sprintf('cmd.php?cmd=search&search=true&form=advanced&server_id=%s&filter=%s=%s',
				$ldapserver->server_id,$attr,$badattr));

			pla_error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href=\'%s\'>search</a> for that entry.'),$attr,$badattr,$entry['dn']['string'],$href['search']));
		}

		$new_entry[$attr] = $val;
	}

$result = $ldapserver->attrModify($entry['dn']['string'],$new_entry);

if (! $result)
	pla_error(_('Could not perform ldap_mod_add operation.'),$ldapserver->error(),$ldapserver->errno());

else {
	$modified_attrs = array_keys($entry['new']['attrs']);
	$modified_attrs[] = 'objectclass';

	$href['complete'] = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s&modified_attrs=%s',
		$ldapserver->server_id,$entry['dn']['encode'],serialize($modified_attrs));

	header(sprintf('Location: %s',$href['complete']));
	die();
}
?>
