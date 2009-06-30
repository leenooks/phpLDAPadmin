<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/update.php,v 1.29.2.1 2007/12/26 09:26:32 wurley Exp $

/**
 * Updates or deletes a value from a specified attribute for a specified dn.
 *
 * Variables that come in on the query string:
 * - dn (rawurlencoded)
 * - update_array (an array in the form expected by PHP's ldap_modify, except for deletions)
 *   (will never be empty: update_confirm.php ensures that)
 *
 * Attribute deletions:
 * To specify that an attribute is to be deleted (whether multi- or single-valued),
 * enter that attribute in the update array like this: attr => ''. For example, to
 * delete the 'sn' attribute from an entry, the update array would look like this:
 * Array (
 *     sn => ''
 * )
 *
 * On success, redirect to template_engine.php. On failure, echo an error.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$entry['dn']['string'] = get_request('dn');
$entry['dn']['encode'] = rawurlencode($entry['dn']['string']);

# If cancel was submited, got back to the edit display.
if (isset($_REQUEST['cancel'])) {
	header(sprintf('Location: cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,$entry['dn']['encode']));
	die();
}

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

$entry['update'] = get_request('update_array','POST',false,array());
$entry['skip'] = get_request('skip_array','POST',false,array());
$failed_attrs = array();

if (! is_array($entry['update']))
	pla_error(_('update_array is malformed. This might be a phpLDAPadmin bug. Please report it.'));

run_hook ('pre_update',
	array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'update_array'=>$entry['update']));

# Check for delete attributes (indicated by the attribute entry appearing like this: attr => ''
foreach ($entry['update'] as $attr => $val) {
	if (! is_array($val)) {
		if (array_key_exists($attr,$entry['skip'])) {
			unset($entry['update'][$attr]);

		} elseif ($val == '') {
			$entry['update'][$attr] = array();

			if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete'))
				pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('delete attribute')));
		} else { # Skip change
			$entry['update'][$attr] = $val;

			if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')
			    && ! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete_value'))
				pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('modify attribute values')));
		}

	} else {
		if (array_key_exists($attr,$entry['skip'])) {
			unset($entry['update'][$attr]);

		} else {
			foreach ($val as $i => $v)
				$entry['update'][$attr][$i] = $v;

			if (! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_add_value')
			    && ! $_SESSION[APPCONFIG]->isCommandAvailable('attribute_delete_value'))
				pla_error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('modify attribute values')));
		}
	}
}

# Call the custom callback for each attribute modification and verify that it should be modified.
foreach ($entry['update'] as $attr_name => $val) {
	# Check to see if this is a unique Attribute
	if ($badattr = $ldapserver->checkUniqueAttr($entry['dn']['string'],$attr_name,$val)) {
		$href['search'] = sprintf('cmd.php?cmd=search&search=true&form=advanced&server_id=%s&filter=%s=%s',
			$ldapserver->server_id,$attr_name,$badattr);

		pla_error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href="%s">search</a> for that entry.'),
			$attr_name,$badattr,$entry['dn']['string'],$href['search']));
	}

	if (run_hook('pre_attr_modify',
		array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'attr_name'=>$attr_name,'new_value'=>$val)) !== true) {

		unset($entry['update'][$attr_name]);
		$failed_attrs[$attr_name] = $val;

	} elseif ($ldapserver->isAttrReadOnly($attr)) {
		pla_error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),
			htmlspecialchars($attr_name)));
	} else {
		// binary values
		if (isset($_SESSION['submitform'][$attr_name])) {
			foreach ($val as $i => $v) {
				if (isset($_SESSION['submitform'][$attr_name][$v])) {
					foreach ($_SESSION['submitform'][$attr_name][$v] as $file) {
						foreach ($file as $data) {
							$entry['update'][$attr_name][$i] = $data;
						}
					}
				}
			}
		}
	}
}

# Perform the modification
$result = $ldapserver->modify($entry['dn']['string'],$entry['update']);
if ($result) {
	# Fire the post modification event to the user's custom callback function.
	$mustRelogin = false;
	foreach ($entry['update'] as $attr_name => $val) {
		run_hook('post_attr_modify',
			array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'attr_name'=>$attr_name,'new_value'=>$val));

		/* Was this a user's password modification who is currently
		 * logged in? If so, they need to logout and log back in
		 * with the new password.
		 */
		if (0 === strcasecmp($attr_name,'userPassword') &&
			in_array($ldapserver->auth_type,array('cookie','session')) &&
			pla_compare_dns($ldapserver->getLoggedInDN(),$entry['dn']['string']) === 0)

			$mustRelogin = true;
	}

	run_hook('post_update',
		array('server_id'=>$ldapserver->server_id,'dn'=>$entry['dn']['string'],'update_array'=>$entry['update']));

	# If the user password was changed, not tell the to relogin.
	if ($mustRelogin) {
			$ldapserver->unsetLoginDN();
			unset_lastactivity($ldapserver);
			include './header.php';

			echo '<body>';

			echo '<br />';
			echo '<center>';
			printf('<b>%s</b>',_('Modification successful!'));
			echo '<br /><br />';
			echo _('Since you changed your password, you must now login again with your new password.');
			echo '<br />';
			printf('<a href="cmd.php?cmd=login_form&server_id=%s">%s...</a>',$ldapserver->server_id, _('Login'));
			echo '</center>';
			echo '</body>';
			echo '</html>';

			exit;
	}

	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$ldapserver->server_id,$entry['dn']['encode']);

	foreach ($entry['update'] as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	foreach ($failed_attrs as $attr => $junk)
		$redirect_url .= "&failed_attrs[]=$attr";

	header("Location: $redirect_url");
	die();

} else {
	pla_error(_('Could not perform ldap_modify operation.'),$ldapserver->error(),$ldapserver->errno());
}
?>
