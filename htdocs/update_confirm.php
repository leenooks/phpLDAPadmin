<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/update_confirm.php,v 1.43.2.13 2008/11/28 14:21:37 wurley Exp $

/**
 * Takes the results of clicking "Save" in template_engine.php and determines which
 * attributes need to be updated (ie, which ones actually changed). Then,
 * we present a confirmation table to the user outlining the changes they
 * are about to make. That form submits directly to update.php, which
 * makes the change.
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';
include './header.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));

$dn = $_POST['dn'];
$old_values = $_POST['old_values'];
$new_values = $_POST['new_values'];
$encoded_dn = rawurlencode($dn);
$rdn = get_rdn($dn);

echo '<body>';
printf('<h3 class="title">%s</h3>',htmlspecialchars($rdn));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));
echo "\n\n";

run_hook('pre_update_array_processing',array('server_id'=>$ldapserver->server_id,
	'dn'=>$dn,'old_values'=>$old_values,'new_values'=>$new_values));

$update_array = array();
foreach ($old_values as $attr => $old_val) {
	# Did the user delete the field?
	if (! isset($new_values[$attr]))
		$update_array[$attr] = '';

	# Did the user change the field?
	elseif ($old_val !== $new_values[$attr]) {
		$new_val = $new_values[$attr];

		# Special case for userPassword attributes
		if (strcasecmp($attr,'userPassword') == 0) {
			foreach ($new_val as $key => $userpassword) {
				if (trim($userpassword))
					$new_val[$key] = password_hash($userpassword,$_POST['enc_type'][$key]);
				else
					unset($new_val[$key]);
			}

			$password_already_hashed = true;

		# Special case for samba password
		} elseif (strcasecmp($attr,'sambaNTPassword') == 0 && trim($new_val[0])) {
			$sambapassword = new smbHash;
			$new_val[0] = $sambapassword->nthash($new_val[0]);

		# Special case for samba password
		} elseif (strcasecmp($attr,'sambaLMPassword') == 0 && trim($new_val[0])) {
			$sambapassword = new smbHash;
			$new_val[0] = $sambapassword->lmhash($new_val[0]);
		}

		# Retest in case our now encoded password is the same.
		if ($new_val === $old_val)
			continue;

		if ($new_val)
			$update_array[$attr] = $new_val;
	}
}

# Check user password with new encoding.
if (isset($new_values['userpassword']) && is_array($new_values['userpassword'])) {
	foreach ($new_values['userpassword'] as $key => $userpassword) {
		if ($userpassword) {
			if ($old_values['userpassword'][$key] == $new_values['userpassword'][$key] && 
				get_enc_type($old_values['userpassword'][$key]) == $_POST['enc_type'][$key])
				continue;

			$new_values['userpassword'][$key] = password_hash($userpassword,$_POST['enc_type'][$key]);
		}
	}

	if ($old_values['userpassword'] != $new_values['userpassword'])
		$update_array['userpassword'] = $new_values['userpassword'];
}

# strip empty vals from update_array and ensure consecutive indices for each attribute
foreach ($update_array as $attr => $val) {
	if (is_array($val)) {
		foreach($val as $i => $v)
			if (null == $v || 0 == strlen($v))
				unset($update_array[$attr][$i]);

		$update_array[$attr] = array_values($update_array[$attr]);
	}
}

/* At this point, the update_array should look like this (example):
  Array(
	cn => Array(
		[0] => 'Dave',
		[1] => 'Bob')
	sn => 'Smith',
	telephoneNumber => '555-1234')
  This array should be ready to be passed to ldap_modify() */

run_hook('post_update_array_processing',array('server_id'=>$ldapserver->server_id,
	'dn'=>$dn,'update_array'=>$update_array));

if (count($update_array) > 0) {
	echo '<br />';
	echo '<center>';
	echo _('Do you want to make these changes?');
	echo '<br /><br />';

	# <!-- Commit button and acompanying form -->
	echo "\n\n";
	echo '<form action="update.php" method="post">';
	echo "\n";
	echo '<table class="confirm">';
	echo "\n";

	printf('<tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr>',
		_('Attribute'),_('Old Value'),_('New Value'),_('Skip'));

	echo "\n\n";
	$counter = 0;

	run_hook('pre_display_update_array',array('server_id'=>$ldapserver->server_id,'dn'=>$dn,
		'update_array'=>$update_array));

	foreach ($update_array as $attr => $new_val) {
		$counter++;

		if (! array_key_exists($attr,$old_values) or ! array_key_exists($attr,$new_values))
			continue;

		printf('<tr class="%s">',$counter%2 ? 'even' : 'odd');
		printf('<td><b>%s</b></td>',htmlspecialchars($attr));
		echo '<td><span style="white-space: nowrap;">';

		if (strcasecmp($attr,'userPassword') == 0) {
			foreach ($old_values[$attr] as $key => $value) {
				if (obfuscate_password_display(get_enc_type($old_values[$attr][$key])))
					echo preg_replace('/./','*',$old_values[$attr][$key]).'<br />';
				else
					echo nl2br(htmlspecialchars(dn_unescape($old_values[$attr][$key]))).'<br />';
			}

		} elseif (is_array($old_values[$attr]))
			foreach ($old_values[$attr] as $v)
				echo nl2br(htmlspecialchars(dn_unescape($v))).'<br />';

		else
			echo nl2br(htmlspecialchars(dn_unescape($old_values[$attr]))).'<br />';

		echo '</span></td>';
		echo '<td><span style="white-space: nowrap;">';

		# Is this a multi-valued attribute?
		if (is_array($new_val)) {
			if (strcasecmp($attr,'userPassword') == 0) {
				foreach ($new_values[$attr] as $key => $value) {
					if (isset($new_val[$key])) {
						if (obfuscate_password_display(get_enc_type($new_val[$key])))
							echo preg_replace('/./','*',$new_val[$key]).'<br />';
						else
							echo htmlspecialchars(dn_unescape($new_val[$key])).'<br />';
					}
				}

			} else {

				foreach ($new_val as $i => $v) {
					if ($v == '') {
						# Remove it from the update array if it's empty
						unset($update_array[$attr][$i]);
						$update_array[$attr] = array_values($update_array[$attr]);

					} else {
						echo nl2br(htmlspecialchars(dn_unescape($v))).'<br />';
					}
				}
			}

			/* was this a multi-valued attribute deletion? If so,
			   fix the $update_array to reflect that per update_confirm.php's
			   expectations */
			if ($update_array[$attr] == array(0=>'') || $update_array[$attr] == array()) {
				$update_array[$attr] = '';
				printf('<span style="color: red">%s</span>',_('[attribute deleted]'));
			}

		} elseif ($new_val != '')
				printf('<span style="color: red">%s</span>',_('[attribute deleted]'));

		echo '</span></td>';

 		printf('<td><input name="skip_array[%s]" type="checkbox" /></td>',htmlspecialchars($attr));
		echo '</tr>'."\n\n";
	}

	run_hook('post_display_update_array',array('server_id'=>$ldapserver->server_id,'dn'=>$dn,
		'update_array'=>$update_array,'index'=>$counter));

	echo '</table><table class="form">';
	echo '<tr>';
	echo '<td>';
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="hidden" name="dn" value="%s" />',$dn);

	foreach ($update_array as $attr => $val) {
		if (is_array($val))
			foreach($val as $i => $v)
				printf('<input type="hidden" name="update_array[%s][%s]" value="%s" />',
					htmlspecialchars($attr),$i,htmlspecialchars($v)); 
		else
			printf('<input type="hidden" name="update_array[%s]" value="%s" />',
				htmlspecialchars($attr),htmlspecialchars($val));
	}

	printf('<input type="submit" value="%s" class="happy" />',_('Commit'));
	echo '</td>';
	echo '<td>';
	printf('<input type="submit" name="cancel" value="%s" class="scary" />',_('Cancel'));
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '</center>';

} else {
	echo '<center>';
	echo _('You made no changes');
	printf(' <a href="template_engine.php?server_id=%s&amp;dn=%s">%s</a>.',
		$ldapserver->server_id,$encoded_dn,_('Go back'));
	echo '</center>';
}

echo '</body>';
?>
