<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/update.php,v 1.25.2.7 2008/11/28 14:21:37 wurley Exp $

/**
 * Updates or deletes a value from a specified attribute for a specified dn.
 *
 * Variables that come in via common.php
 * - server_id
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

$dn = $_POST['dn'];
$encoded_dn = rawurlencode($dn);

# If cancel was submited, got back to the edit display.
if (isset($_REQUEST['cancel'])) {
	header(sprintf('Location: template_engine.php?server_id=%s&dn=%s',$ldapserver->server_id,$encoded_dn));
	die();
}

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$update_array = isset($_POST['update_array']) ? $_POST['update_array'] : array();
$skip_array = isset($_POST['skip_array']) ? $_POST['skip_array'] : array();
$failed_attrs = array();

if (! is_array($update_array))
	pla_error(_('update_array is malformed. This might be a phpLDAPadmin bug. Please report it.'));


# Check for delete attributes (indicated by the attribute entry appearing like this: attr => ''
foreach ($update_array as $attr => $val) {
	if (! is_array($val)) {
		if (array_key_exists($attr,$skip_array))
			unset($update_array[$attr]);
		elseif ($val == '')
			$update_array[$attr] = array();

		# Skip change
		else {
			if (is_dn_string($val) || $ldapserver->isDNAttr($attr))
				$val=dn_escape($val);
			$update_array[$attr] = $val;
		}
	} else {
		if (array_key_exists($attr,$skip_array))
			unset($update_array[$attr]);

		else
			foreach ($val as $i => $v) {
				if (is_dn_string($v) || $ldapserver->isDNAttr($attr))
					$v=dn_escape($v);
				$update_array[$attr][$i] = $v;
			}
	}
}
run_hook ('pre_update',array('server_id'=>$ldapserver->server_id,'dn'=>$dn,'update_array'=>$update_array));
#die();

/* Call the custom callback for each attribute modification
   and verify that it should be modified.*/
foreach ($update_array as $attr_name => $val) {
	# Check to see if this is a unique Attribute
	if ($badattr = $ldapserver->checkUniqueAttr($dn,$attr_name,$val)) {
		$search_href = sprintf('search.php?search=true&form=advanced&server_id=%s&filter=%s=%s',
			$ldapserver->server_id,$attr_name,$badattr);

		pla_error(sprintf(_('Your attempt to add <b>%s</b> (<i>%s</i>) to <br><b>%s</b><br> is NOT allowed. That attribute/value belongs to another entry.<p>You might like to <a href="%s">search</a> for that entry.'),
			$attr_name,$badattr,$dn,$search_href));
	}

	if (run_hook('pre_attr_modify',
		array('server_id'=>$ldapserver->server_id,'dn'=>$dn,'attr_name'=>$attr_name,'new_value'=>$val)) !== true) {

		unset($update_array[$attr_name]);
		$failed_attrs[$attr_name] = $val;

	} elseif ($ldapserver->isAttrReadOnly($attr))
		pla_error(sprintf(_('The attribute "%s" is flagged as read-only in the phpLDAPadmin configuration.'),
			htmlspecialchars($attr_name)));
}

# Perform the modification
$res = $ldapserver->modify($dn,$update_array);
if ($res) {
	# Fire the post modification event to the user's custom callback function.
	$mustRelogin = false;
	foreach ($update_array as $attr_name => $val) {
		run_hook('post_attr_modify',
			array('server_id'=>$ldapserver->server_id,'dn'=>$dn,'attr_name'=>$attr_name,'new_value'=>$val));

		/* Was this a user's password modification who is currently
		   logged in? If so, they need to logout and log back in
		   with the new password. */
		if (0 === strcasecmp($attr_name,'userPassword') &&
			in_array($ldapserver->auth_type,array('cookie','session')) &&
			pla_compare_dns($ldapserver->getLoggedInDN(),$dn) === 0)

			$mustRelogin = true;
	}

	run_hook ('post_update',array ('server_id' => $ldapserver->server_id,'dn' => $dn,'update_array' => $update_array));

	# If the user password was changed, not tell the to relogin.
	if ($mustRelogin) {
			$ldapserver->unsetLoginDN();
			unset_lastactivity($ldapserver);
			include './header.php';

			echo '<body>';
			echo '<script type="text/javascript" language="javascript">';
			echo 'parent.left_frame.location.reload();';
			echo '</script>'."\n\n";

			echo '<br />';
			echo '<center>';
			printf('<b>%s</b>',_('Modification successful!'));
			echo '<br /><br />';
			echo _('Since you changed your password, you must now login again with your new password.');
			echo '<br />';
			printf('<a href="login_form.php?server_id=%s">%s...</a>',$ldapserver->server_id, _('Login'));
			echo '</center>';
			echo '</body>';
			echo '</html>';

			exit;
	}

	$redirect_url = sprintf('template_engine.php?server_id=%s&dn=%s',$ldapserver->server_id,$encoded_dn);

	foreach ($update_array as $attr => $junk)
		$redirect_url .= "&modified_attrs[]=$attr";

	foreach ($failed_attrs as $attr => $junk)
		$redirect_url .= "&failed_attrs[]=$attr";

	header("Location: $redirect_url");

} else {
	pla_error(_('Could not perform ldap_modify operation.'),$ldapserver->error(),$ldapserver->errno());
}
?>
