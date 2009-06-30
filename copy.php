<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/copy.php,v 1.35 2005/09/25 16:11:44 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Vars that come in as POST vars
 *  - source_dn (rawurlencoded)
 *  - new_dn (form element)
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

$server_id_src = (isset($_POST['server_id']) ? $_POST['server_id'] : '');
$server_id_dst = (isset($_POST['dest_server_id']) ? $_POST['dest_server_id'] : '');

$ldapserver_src = $ldapservers->Instance($server_id_src);
$ldapserver_dst = $ldapservers->Instance($server_id_dst);

if ($ldapserver_dst->isReadOnly())
	pla_error($lang['copy_server_read_only']);

if (! $ldapserver_src->haveAuthInfo() || ! $ldapserver_dst->haveAuthInfo())
	pla_error($lang['not_enough_login_info']);

$dn_src = $_POST['old_dn'];
$dn_dst = $_POST['new_dn'];
$do_recursive = (isset($_POST['recursive']) && $_POST['recursive'] == 'on') ? true : false;
$do_remove = (isset($_POST['remove']) && $_POST['remove'] == 'yes') ? true : false;
$encoded_dn = rawurlencode($dn_src);

include './header.php';

# Error checking
if (0 == strlen(trim($dn_dst)))
	pla_error($lang['copy_dest_dn_blank']);

if (pla_compare_dns($dn_src,$dn_dst) == 0 && $server_id_src == $server_id_dst)
	pla_error($lang['copy_source_dest_dn_same']);

if (dn_exists($ldapserver_dst,$dn_dst))
	pla_error(sprintf($lang['copy_dest_already_exists'],pretty_print_dn($dn_dst)));

if (! dn_exists($ldapserver_dst,get_container($dn_dst)))
	pla_error(sprintf($lang['copy_dest_container_does_not_exist'],pretty_print_dn(get_container($dn_dst))));

if ($do_recursive) {
	$filter = isset($_POST['filter']) ? $_POST['filter'] : '(objectClass=*)';

	# Build a tree similar to that of the tree browser to give to r_copy_dn
	$snapshot_tree = array();
	print '<body>';
	printf('<h3 class="title">%s%s</h3>',$lang['copy_copying'],htmlspecialchars($dn_src));
	printf('<h3 class="subtitle">%s</h3>',$lang['copy_recursive_copy_progress']);
	print '<br /><br />';
	print '<small>';
	print $lang['copy_building_snapshot'];

	flush();

	$snapshot_tree = build_tree($ldapserver_src,$dn_src,array(),$filter);
	printf('<span style="color:green">%s</span><br />',$lang['success']);
	flush();

	# Prevent script from bailing early on a long delete
	@set_time_limit(0);

	$copy_result = r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshot_tree,$dn_src,$dn_dst);
	print '</small>';

} else {
	$copy_result = copy_dn($ldapserver_src,$ldapserver_dst,$dn_src,$dn_dst);
}

if ($copy_result) {
	$edit_url = sprintf('edit.php?server_id=%s&dn=%s',$server_id_dst,rawurlencode($dn_dst));
	$new_rdn = get_rdn($dn_dst);
	$container = get_container($dn_dst);

	if (array_key_exists('tree',$_SESSION)) {
	        # do we not have a tree and tree icons yet? Build a new ones.
		initialize_session_tree();
		$tree = $_SESSION['tree'];
		$tree_icons = $_SESSION['tree_icons'];

		if (isset($tree[$server_id_dst][$container])) {
			$tree[$server_id_dst][$container][] = $dn_dst;
			sort($tree[$server_id_dst][$container]);
			$tree_icons[$server_id_dst][$dn_dst] = get_icon($ldapserver_dst,$dn_dst);

			$_SESSION['tree'] = $tree;
			$_SESSION['tree_icons'] = $tree_icons;
			session_write_close();
		}
	}
?>

	<center>
	<?php printf('%s<a href="%s">%s</a>',$lang['copy_successful_like_to'],$edit_url,$lang['copy_view_new_entry']) ?>
	</center>
	<!-- refresh the tree view (with the new DN renamed)
	and redirect to the edit_dn page -->
	<script language="javascript">
		parent.left_frame.location.reload();
	</script>
	</body>
	</html>

<?php
	if ($do_remove) {
		sleep(2);
		$delete_url = sprintf('delete_form.php?server_id=%s&dn=%s',$server_id_dst,rawurlencode($dn_src));
?>

		<!-- redirect to the delete form -->
		<script language="javascript">
			parent.right_frame.location="<?php echo $delete_url; ?>"
		</script>
	<?php }

} else {
	exit;
}

function r_copy_dn($ldapserver_src,$ldapserver_dst,$tree,$root_dn,$dn_dst) {
	debug_log(sprintf('r_copy_dn: Entered with (%s,%s,%s,%s,%s)',
		$ldapserver_src->server_id,$ldapserver_dst->server_id,serialize($tree),$root_dn,$dn_dst),2);

        global $lang;

	printf('<nobr>%s %s...',$lang['copy_copying'],htmlspecialchars($root_dn));
	flush();

	$copy_result = copy_dn($ldapserver_src,$ldapserver_dst,$root_dn,$dn_dst);

	if (! $copy_result)
		return false;

	printf('<span style="color:green">%s</span></nobr><br />',$lang['success']);
	flush();

	$children = isset($tree[$root_dn]) ? $tree[$root_dn] : null;
	if (is_array($children) && count($children) > 0) {
		foreach($children as $child_dn) {
			$child_rdn = get_rdn($child_dn);
			$new_dest_dn = sprintf('%s,%s',$child_rdn,$dn_dst);
			r_copy_dn($ldapserver_src,$ldapserver_dst,$tree,$child_dn,$new_dest_dn);
		}

	} else {
		return true;
	}

	return true;
}

function copy_dn($ldapserver_src,$ldapserver_dst,$dn_src,$dn_dst) {
	debug_log(sprintf('copy_dn: Entered with (%s,%s,%s,%s)',
		$ldapserver_src->server_id,$ldapserver_dst->server_id,$dn_src,$dn_dst),2);

	global $lang;

	$new_entry = get_object_attrs($ldapserver_src,$dn_src);

	# modify the prefix-value (ie "bob" in cn=bob) to match the destination DN's value.
	$rdn_attr = substr($dn_dst,0,strpos($dn_dst,'='));
	$rdn_value = get_rdn($dn_dst);
	$rdn_value = substr($rdn_value,strpos($rdn_value,'=') + 1);
	$new_entry[$rdn_attr] = $rdn_value;

	# don't need a dn attribute in the new entry
	unset($new_entry['dn']);

	# Check the user-defined custom call back first
	if (true === run_hook('pre_entry_create',
		array ('server_id'=>$ldapserver_dst->server_id,'dn'=>$dn_dst,'attrs'=>$new_entry))) {

		$add_result = @ldap_add($ldapserver_dst->connect(),$dn_dst,$new_entry);
		if (! $add_result) {
			run_hook('post_entry_create',array('server_id'=>$ldapserver_dst->server_id,
				'dn'=>$dn_dst,'attrs'=>$new_entry));

			print '</small><br /><br />';
			pla_error($lang['copy_failed'] . $dn_dst,ldap_error($ldapserver_dst->connect()),ldap_errno($ldapserver_dst->connect()));
		}

		return $add_result;

	} else {
		return false;
	}
}

/**
 * @param object $ldapserver
 * @param dn $dn
 * @param array $tree
 * @param string $filter
 */
function build_tree($ldapserver,$dn,$tree,$filter='(objectClass=*)') {
	debug_log(sprintf('build_tree: Entered with (%s,%s,%s,%s)',
		$ldapserver->server_id,$dn,serialize($tree),$filter),2);

	$children = get_container_contents($ldapserver,$dn,0,$filter);

	if (is_array($children) && count($children) > 0) {
		$tree[$dn] = $children;
		foreach ($children as $child_dn)
			$tree = build_tree($ldapserver,$child_dn,$tree,$filter);
	}

	debug_log(sprintf('build_tree: Returning (%s)',serialize($tree)),1);
	return $tree;
}
?>
