<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete.php,v 1.23.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * Deletes a DN and presents a "job's done" message.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as POST vars:
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

$dn = $_POST['dn'];
$encoded_dn = rawurlencode($dn);

if (is_null($dn))
	pla_error($lang['you_must_specify_a_dn']);

dn_exists($ldapserver,$dn) or pla_error(sprintf($lang['no_such_entry'], '<b>' . pretty_print_dn($dn) . '</b>'));

// Check the user-defined custom callback first.
if (run_hook('pre_entry_delete',array('server_id' => $ldapserver->server_id,'dn' => $dn)))
	$del_result = @ldap_delete($ldapserver->connect(), $dn);

else
	pla_error(sprintf($lang['could_not_delete_entry'],'<b>'.pretty_print_dn($dn).'</b>'));

if ($del_result) {

	# Custom callback
	run_hook('post_entry_delete',array('server_id' => $ldapserver->server_id,'dn' => $dn));

	# kill the DN from the tree browser session variable and
	# refresh the tree viewer frame (left_frame)
	if (array_key_exists('tree', $_SESSION)) {
		$tree = $_SESSION['tree'];

		if (isset($tree[$ldapserver->server_id]) && is_array($tree[$ldapserver->server_id])) {

			# does it have children? (it shouldn't, but hey, you never know)
			if (isset($tree[$ldapserver->server_id][$dn]))
				unset($tree[$ldapserver->server_id][$dn]);

			# search and destroy
			foreach ($tree[$ldapserver->server_id] as $tree_dn => $subtree)
				foreach ($subtree as $key => $sub_tree_dn)
					if (0 == strcasecmp($sub_tree_dn, $dn))
						unset($tree[$ldapserver->server_id][$tree_dn][$key]);

			$_SESSION['tree'] = $tree;
		}
		session_write_close();
	}

	include './header.php'; ?>

	<script language="javascript">
		parent.left_frame.location.reload();
	</script>

	<br />
	<br />
	<center><?php echo sprintf($lang['entry_deleted_successfully'],'<b>'.pretty_print_dn($dn).'</b>'); ?></center>

<?php
} else {
	pla_error(sprintf($lang['could_not_delete_entry'], '<b>' . pretty_print_dn($dn) . '</b>'),
		  $ldapserver->error(), $ldapserver->errno());
}
?>
