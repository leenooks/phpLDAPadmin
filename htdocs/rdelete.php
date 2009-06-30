<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/rdelete.php,v 1.22.2.1 2005/10/09 09:07:21 wurley Exp $

/**
 * Recursively deletes the specified DN and all of its children
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
$rdn = get_rdn($dn);

if (! $dn)
	pla_error($lang['you_must_specify_a_dn']);

dn_exists($ldapserver,$dn) or pla_error(sprintf($lang['no_such_entry'],htmlspecialchars($dn)));

include './header.php';

echo "<body>\n";
echo "<h3 class=\"title\">".sprintf($lang['deleting_dn'],htmlspecialchars($rdn))."</h3>\n";
echo "<h3 class=\"subtitle\">".$lang['recursive_delete_progress']."</h3>";
echo "<br /><br />";
echo "<small>\n";

flush();

// prevent script from bailing early on a long delete
@set_time_limit(0);

$del_result = pla_rdelete($ldapserver,$dn);
echo "</small><br />\n";

if ($del_result) {
	# kill the DN from the tree browser session variable and
	# refresh the tree viewer frame (left_frame)

	if (array_key_exists('tree',$_SESSION)) {
		$tree = $_SESSION['tree'];

		# does it have children? (it shouldn't, but hey, you never know)
		if (isset($tree[$ldapserver->server_id][$dn]))
			unset($tree[$ldapserver->server_id][$dn]);

		# Get a tree in the session if not already gotten
		initialize_session_tree();

		# search and destroy from the tree sesssion
		foreach ($tree[$ldapserver->server_id] as $tree_dn => $subtree)
			foreach ($subtree as $key => $sub_tree_dn)
				if (0 == strcasecmp($sub_tree_dn,$dn))
					unset($tree[$ldapserver->server_id][$tree_dn][$key]);
	}

	$_SESSION['tree'] = $tree;
	session_write_close();

?>

	<script language="javascript">
		parent.left_frame.location.reload();
	</script>

<?php

	echo sprintf($lang['entry_and_sub_tree_deleted_successfully'],'<b>'.htmlspecialchars($dn).'</b>');

} else {
        pla_error(sprintf($lang['could_not_delete_entry'],htmlspecialchars($dn)),
		  $ldapserver->error(),$ldapserver->errno());
}

exit;

function pla_rdelete($ldapserver,$dn) {
	global $lang;
	$children = get_container_contents($ldapserver,$dn);

	if (! is_array($children) || count($children) == 0) {
		echo "<nobr>".sprintf($lang['deleting_dn'],htmlspecialchars($dn))."...";
		flush();

		if (run_hook('pre_entry_delete',array('server_id' => $ldapserver->server_id,'dn' => $dn)))

			if (@ldap_delete($ldapserver->connect(),$dn)) {
		                run_hook ('post_entry_delete',
					array('server_id' => $ldapserver->server_id,'dn' => $dn));
				echo " <span style=\"color:green\">".$lang['success']."</span></nobr><br />\n";
				return true;

			} else {
			        pla_error(sprintf($lang['failed_to_delete_entry'],htmlspecialchars($dn)),
					  $ldapserver->error(),$ldapserver->errno());
			}
	} else {
		foreach ($children as $child_dn) {
			pla_rdelete($ldapserver,$child_dn);
		}

		echo "<nobr>".sprintf($lang['deleting_dn'],htmlspecialchars($dn))."...";
		flush();

		if (true === run_hook ('pre_entry_delete',array('server_id' => $ldapserver->server_id,'dn' => $dn)))
			if (@ldap_delete($ldapserver->connect(),$dn)) {
		                run_hook ('post_entry_delete',
					array('server_id' => $ldapserver->server_id,'dn' => $dn));

				echo " <span style=\"color:green\">".$lang['success']."</span></nobr><br />\n";
				return true;

			} else {
			        pla_error(sprintf($lang['failed_to_delete_entry'],htmlspecialchars($dn)),
					  $ldapserver->error(),$ldapserver->errno());
			}
	}
}
?>
