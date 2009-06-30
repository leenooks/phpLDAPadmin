<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/copy.php,v 1.44.2.3 2008/12/12 12:20:22 wurley Exp $

/**
 * Copies a given object to create a new one.
 *
 * Vars that come in as POST vars
 * - source_dn (rawurlencoded)
 * - new_dn (form element)
 * - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if (! $_SESSION[APPCONFIG]->isCommandAvailable('entry_move'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('copy entry')),'error','index.php');

$entry = array();
$entry['src']['id'] = get_request('server_id');
$entry['dst']['id'] = get_request('dest_server_id');

$entry['src']['ldapserver'] = $_SESSION[APPCONFIG]->ldapservers->Instance($entry['src']['id']);
$entry['dst']['ldapserver'] = $_SESSION[APPCONFIG]->ldapservers->Instance($entry['dst']['id']);

if ($entry['dst']['ldapserver']->isReadOnly())
	error(_('Destination server is currently READ-ONLY.'),'error','index.php');

if (! $entry['src']['ldapserver']->haveAuthInfo() || ! $entry['dst']['ldapserver']->haveAuthInfo())
	error(_('Not enough information to login to server. Please check your configuration.'),'error','index.php');

$entry['src']['dn'] = get_request('old_dn');
$entry['dst']['dn'] = get_request('new_dn');
$entry['src']['recursive'] = (get_request('recursive') == 'on') ? true : false;
$entry['src']['remove'] = (get_request('remove') == 'yes') ? true : false;

# Error checking
if (strlen(trim($entry['dst']['dn'])) == 0)
	error(_('You left the destination DN blank.'),'error','index.php');

if (pla_compare_dns($entry['src']['dn'],$entry['dst']['dn']) == 0 && $entry['src']['id'] == $entry['dst']['id'])
	error(_('The source and destination DN are the same.'),'error','index.php');

if ($entry['dst']['ldapserver']->dnExists($entry['dst']['dn']))
	error(sprintf(_('The destination entry (%s) already exists.'),pretty_print_dn($entry['dst']['dn'])),'error','index.php');

if (! $entry['dst']['ldapserver']->dnExists(get_container($entry['dst']['dn'])))
	error(sprintf(_('The destination container (%s) does not exist.'),pretty_print_dn(get_container($entry['dst']['dn']))),'error','index.php');

if ($entry['src']['recursive']) {
	$filter = get_request('filter','POST',false,'(objectClass=*)');

	# Build a tree similar to that of the tree browser to give to r_copy_dn
	$snapshot_tree = array();
	printf('<h3 class="title">%s%s</h3>',_('Copying '),htmlspecialchars($entry['src']['dn']));
	printf('<h3 class="subtitle">%s</h3>',_('Recursive copy progress'));
	print '<br /><br />';
	print '<small>';
	print _('Building snapshot of tree to copy... ');

	$snapshot_tree = build_tree($entry['src']['ldapserver'],$entry['src']['dn'],array(),$filter);
	printf('<span style="color:green">%s</span><br />',_('Success'));

	# Prevent script from bailing early on a long delete
	@set_time_limit(0);

	$copy_result = r_copy_dn($entry['src']['ldapserver'],$entry['dst']['ldapserver'],$snapshot_tree,$entry['src']['dn'],$entry['dst']['dn']);
	# @todo: This is not showing the complete results - only the children of the dst - need to look at.
	$copy_message = $copy_result;
	print '</small>';

} else {
	$copy_result = copy_dn($entry['src']['ldapserver'],$entry['dst']['ldapserver'],$entry['src']['dn'],$entry['dst']['dn']);
	$copy_message = sprintf('%s DN%s <b>%s</b> %s',_('Copy successful!'),_(':'),htmlspecialchars($entry['dst']['dn']),_('has been created.'));
}

if ($copy_result) {
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',$entry['dst']['id'],rawurlencode($entry['dst']['dn']));
	$new_rdn = get_rdn($entry['dst']['dn']);
	$container = get_container($entry['dst']['dn']);

	if ($entry['src']['remove'])
		$redirect_url = sprintf('cmd.php?cmd=delete_form&server_id=%s&dn=%s',$entry['src']['id'],rawurlencode($entry['src']['dn']));

	system_message(array(
		'title'=>_('Copy Entry'),
		'body'=>$copy_message,
		'type'=>'info'),
		$redirect_url);
}

function r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshottree,$root_dn,$dn_dst) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver_src->server_id,$ldapserver_dst->server_id,$snapshottree,$root_dn,$dn_dst);

	$copy_message = array();

	$copy_result = copy_dn($ldapserver_src,$ldapserver_dst,$root_dn,$dn_dst);

	if (! $copy_result)
		return false;

	$copy_message[] = sprintf('%s DN: <b>%s</b> %s',_('Copy successful!'),htmlspecialchars($dn_dst),_('has been created.'));

	$children = isset($snapshottree[$root_dn]) ? $snapshottree[$root_dn] : null;
	if (is_array($children) && count($children) > 0) {
		foreach($children as $child_dn) {
			$child_rdn = get_rdn($child_dn);
			$new_dest_dn = sprintf('%s,%s',$child_rdn,$dn_dst);
			$copy_result = r_copy_dn($ldapserver_src,$ldapserver_dst,$snapshottree,$child_dn,$new_dest_dn);
			$copy_message[] = array_shift($copy_result);
		}
	}

	return $copy_message;
}

function copy_dn($ldapserver_src,$ldapserver_dst,$dn_src,$dn_dst) {
	if (DEBUG_ENABLED)
		debug_log('Entered with (%s,%s,%s,%s)',17,__FILE__,__LINE__,__METHOD__,
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
			echo '</small><br /><br />';
			system_message(array(
				'title'=>_('Failed to copy DN.').sprintf(' (%s)',$dn_dst),
				'body'=>ldap_error_msg($ldapserver->error(),$ldapserver->errno()),
				'type'=>'error'));

		} else {
			run_hook('post_entry_create',
				array('server_id'=>$ldapserver_dst->server_id,'dn'=>$dn_dst,'attrs'=>$new_entry));
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
		debug_log('Entered with (%s,%s,%s)',1,__FILE__,__LINE__,__METHOD__,
			$ldapserver->server_id,$dn,$buildtree);

	# we search all children, not only the visible children in the tree
	$children = $ldapserver->getContainerContents($dn,0);

	if (is_array($children) && count($children) > 0) {
		$buildtree[$dn] = $children;
		foreach ($children as $child_dn)
			$buildtree = build_tree($ldapserver,$child_dn,$buildtree);
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,__FILE__,__LINE__,__METHOD__,$buildtree);

	return $buildtree;
}
?>
