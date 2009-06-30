<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/copy.php,v 1.36.2.12 2007/03/18 01:31:19 wurley Exp $

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
	pla_error(_('Destination server is currently READ-ONLY.'));

if (! $ldapserver_src->haveAuthInfo() || ! $ldapserver_dst->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn_src = $_POST['old_dn'];
$dn_dst = $_POST['new_dn'];
$do_recursive = (isset($_POST['recursive']) && $_POST['recursive'] == 'on') ? true : false;
$do_remove = (isset($_POST['remove']) && $_POST['remove'] == 'yes') ? true : false;

include './header.php';

# Error checking
if (0 == strlen(trim($dn_dst)))
	pla_error(_('You left the destination DN blank.'));

if (pla_compare_dns($dn_src,$dn_dst) == 0 && $server_id_src == $server_id_dst)
	pla_error(_('The source and destination DN are the same.'));

if ($ldapserver_dst->dnExists($dn_dst))
	pla_error(sprintf(_('The destination entry (%s) already exists.'),pretty_print_dn($dn_dst)));

if (! $ldapserver_dst->dnExists(get_container($dn_dst)))
	pla_error(sprintf(_('The destination container (%s) does not exist.'),pretty_print_dn(get_container($dn_dst))));

if ($do_recursive) {
	$filter = isset($_POST['filter']) ? $_POST['filter'] : '(objectClass=*)';

	# Build a tree similar to that of the tree browser to give to r_copy_dn
	$snapshot_tree = array();
	print '<body>';
	printf('<h3 class="title">%s%s</h3>',_('Copying '),htmlspecialchars($dn_src));
	printf('<h3 class="subtitle">%s</h3>',_('Recursive copy progress'));
	print '<br /><br />';
	print '<small>';
	print _('Building snapshot of tree to copy... ');

	flush();

	$snapshot_tree = build_tree($ldapserver_src,$dn_src,array(),$filter);
	printf('<span style="color:green">%s</span><br />',_('Success'));
	flush();

	# Prevent script from bailing early on a long delete
	@set_time_limit(0);

	$copy_result = r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshot_tree,$dn_src,$dn_dst);
	print '</small>';

} else {
	$copy_result = copy_dn($ldapserver_src,$ldapserver_dst,$dn_src,$dn_dst);
}

if ($copy_result) {
	$edit_url = sprintf('template_engine.php?server_id=%s&amp;dn=%s',$server_id_dst,rawurlencode($dn_dst));
	$new_rdn = get_rdn($dn_dst);
	$container = get_container($dn_dst);

	printf('<center>%s<a href="%s">%s</a></center>',_('Copy successful! Would you like to '),$edit_url,_('view the new entry'));
	echo '<!-- refresh the tree view (with the new DN renamed) and redirect to the edit_dn page -->';
	echo '<script type="text/javascript" language="javascript">parent.left_frame.location.reload();</script>';
	echo '</body></html>';

	if ($do_remove) {
		sleep(2);
		$delete_url = sprintf('delete_form.php?server_id=%s&dn=%s',$server_id_src,rawurlencode($dn_src));
		echo '<!-- redirect to the delete form -->';
		printf('<script type="text/javascript" language="javascript">parent.right_frame.location="%s" </script>',$delete_url);
	}
}

function r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshottree,$root_dn,$dn_dst) {
        if (DEBUG_ENABLED)
		debug_log('r_copy_dn: Entered with (%s,%s,%s,%s,%s)',1,
			$ldapserver_src->server_id,$ldapserver_dst->server_id,$snapshottree,$root_dn,$dn_dst);

	printf('<span style="white-space: nowrap;">%s %s...',_('Copying'),htmlspecialchars($root_dn));
	flush();

	$copy_result = copy_dn($ldapserver_src,$ldapserver_dst,$root_dn,$dn_dst);

	if (! $copy_result)
		return false;

	printf('<span style="color:green">%s</span><br />',_('Success'));
	flush();

	$children = isset($snapshottree[$root_dn]) ? $snapshottree[$root_dn] : null;
	if (is_array($children) && count($children) > 0) {
		foreach($children as $child_dn) {
			$child_rdn = get_rdn($child_dn);
			$new_dest_dn = sprintf('%s,%s',$child_rdn,$dn_dst);
			r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshottree,$child_dn,$new_dest_dn);
		}

	} else {
		return true;
	}

	return true;
}

function copy_dn($ldapserver_src,$ldapserver_dst,$dn_src,$dn_dst) {
        if (DEBUG_ENABLED)
	        debug_log('copy_dn: Entered with (%s,%s,%s,%s)',17,
			$ldapserver_src->server_id,$ldapserver_dst->server_id,$dn_src,$dn_dst);

	$new_entry = $ldapserver_src->getDNAttrs($dn_src);

	# modify the prefix-value (ie "bob" in cn=bob) to match the destination DN's value.
	$rdn_attr = substr($dn_dst,0,strpos($dn_dst,'='));
	$rdn_value = get_rdn($dn_dst);
	$rdn_value = substr($rdn_value,strpos($rdn_value,'=') + 1);
	$new_entry[$rdn_attr] = $rdn_value;

	# don't need a dn attribute in the new entry
	unset($new_entry['dn']);

	# Check the user-defined custom call back first
	if (run_hook('pre_entry_create',
		array ('server_id'=>$ldapserver_dst->server_id,'dn'=>$dn_dst,'attrs'=>$new_entry))) {

		$add_result = $ldapserver_dst->add($dn_dst,$new_entry);
		if (! $add_result) {
			run_hook('post_entry_create',
				array('server_id'=>$ldapserver_dst->server_id,'dn'=>$dn_dst,'attrs'=>$new_entry));

			echo '</small><br /><br />';
			pla_error(_('Failed to copy DN: ').$dn_dst,$ldapserver_dst->error(),$ldapserver_dst->errno());
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
function build_tree($ldapserver,$dn,$buildtree) {
	if (DEBUG_ENABLED)
		debug_log('build_tree: Entered with (%s,%s,%s)',1,
			$ldapserver->server_id,$dn,$buildtree);

	# we search all children, not only the visible children in the tree
	$children = $ldapserver->getContainerContents($dn,0);

	if (is_array($children) && count($children) > 0) {
		$buildtree[$dn] = $children;
		foreach ($children as $child_dn)
			$buildtree = build_tree($ldapserver,$child_dn,$buildtree);
	}

	if (DEBUG_ENABLED)
		debug_log('build_tree: Returning (%s)',1,$buildtree);

	return $buildtree;
}
?>
