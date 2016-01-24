<?php
/**
 * Copies a given object to create a new one.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DNs we are working with
$request = array();
$request['dnSRC'] = get_request('dn_src');
$request['dnDST'] = get_request('dn_dst');

$ldap = array();
$ldap['SRC'] = $_SESSION[APPCONFIG]->getServer(get_request('server_id_src'));
$ldap['DST'] = $_SESSION[APPCONFIG]->getServer(get_request('server_id_dst'));

# Error checking
if (! trim($request['dnDST']))
	error(_('You left the destination DN blank.'),'error','index.php');

if ($ldap['DST']->isReadOnly())
	error(_('Destination server is currently READ-ONLY.'),'error','index.php');

if ($ldap['DST']->dnExists($request['dnDST']))
	error(sprintf(_('The destination entry (%s) already exists.'),pretty_print_dn($request['dnDST'])),'error','index.php');

if (! $ldap['DST']->dnExists($ldap['DST']->getContainer($request['dnDST'])))
	error(sprintf(_('The destination container (%s) does not exist.'),
		pretty_print_dn($ldap['DST']->getContainer($request['dnDST']))),'error','index.php');

if (pla_compare_dns($request['dnSRC'],$request['dnDST']) == 0 && $ldap['SRC']->getIndex() == $ldap['DST']->getIndex())
	error(_('The source and destination DN are the same.'),'error','index.php');

$request['recursive'] = (get_request('recursive') == 'on') ? true : false;
$request['remove'] = (get_request('remove') == 'yes') ? true : false;

if ($request['recursive']) {
	$filter = get_request('filter','POST',false,'(objectClass=*)');

	# Build a tree similar to that of the tree browser to give to r_copy_dn
	$ldap['tree'] = array();
	printf('<h3 class="title">%s%s</h3>',_('Copying '),$request['dnSRC']);
	printf('<h3 class="subtitle">%s</h3>',_('Recursive copy progress'));
	print '<br /><br />';

	print '<small>';
	printf ('%s...',_('Building snapshot of tree to copy'));

	$ldap['tree'] = build_tree($ldap['SRC'],$request['dnSRC'],array(),$filter);
	printf('<span style="color:green">%s</span><br />',_('Success'));

	# Prevent script from bailing early on a long delete
	@set_time_limit(0);

	$copy_result = r_copy_dn($ldap['SRC'],$ldap['DST'],$ldap['tree'],$request['dnSRC'],$request['dnDST'],$request['remove']);
	$copy_message = $copy_result;
	print '</small>';

} else {
	if ($_SESSION[APPCONFIG]->getValue('confirm','copy') && !$request['remove']) {
		$request['pageSRC'] = new TemplateRender($ldap['SRC']->getIndex(),get_request('template','REQUEST',false,null));
		$request['pageSRC']->setDN($request['dnSRC']);
		$request['pageSRC']->accept(true);

		$request['pageDST'] = new TemplateRender($ldap['DST']->getIndex(),get_request('template','REQUEST',false,'none'));
		$request['pageDST']->setContainer($app['server']->getContainer($request['dnDST']));
		$request['pageDST']->accept(true);

		$request['templateSRC'] = $request['pageSRC']->getTemplate();
		$request['templateDST'] = $request['pageDST']->getTemplate();

		$request['templateDST']->copy($request['templateSRC'],get_rdn($request['dnDST']),true);

		# Set all attributes with a values as shown, and remove the add value options
		foreach ($request['templateDST']->getAttributes(true) as $sattribute)
			if ($sattribute->getValues() && ! $sattribute->isInternal()) {
				$sattribute->show();
				$sattribute->setMaxValueCount(count($sattribute->getValues()));
			}

		$request['pageDST']->accept();

		return;

	} else {
		$copy_result = copy_dn($ldap['SRC'],$ldap['DST'],$request['dnSRC'],$request['dnDST'],$request['remove']);

		if ($copy_result)
			$copy_message = sprintf('%s %s: <b>%s</b> %s',
				$request['remove'] ? _('Move successful') : _('Copy successful'),
				_('DN'),$request['dnDST'],_('has been created.'));
		else
			$copy_message = sprintf('%s %s: <b>%s</b> %s',
				$request['remove'] ? _('Move NOT successful') : _('Copy NOT successful'),
				_('DN'),$request['dnDST'],_('has NOT been created.'));
	}
}

if ($copy_result) {
	$redirect_url = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s&refresh=SID_%s_nodes&noheader=1',
		$ldap['DST']->getIndex(),rawurlencode($request['dnDST']),$ldap['DST']->getIndex());

	system_message(array(
		'title'=>_('Copy Entry'),
		'body'=>$copy_message,
		'type'=>'info'),
		$redirect_url);
}

function r_copy_dn($serverSRC,$serverDST,$snapshottree,$dnSRC,$dnDST,$remove) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$copy_message = array();

	$children = isset($snapshottree[$dnSRC]) ? $snapshottree[$dnSRC] : null;

	# If we have children, then we need to copy, then delete for a move
	if (is_array($children) && count($children)) {

		$copy_result = copy_dn($serverSRC,$serverDST,$dnSRC,$dnDST,false);

		if (! $copy_result)
			return false;

		array_push($copy_message,sprintf('%s %s: <b>%s</b> %s',_('Copy successful'),_('DN'),$dnDST,_('has been created.')));

		$hadError = false;
		foreach ($children as $child_dn) {
			$dnDST_new = sprintf('%s,%s',get_rdn($child_dn),$dnDST);
			$copy_result = r_copy_dn($serverSRC,$serverDST,$snapshottree,$child_dn,$dnDST_new,$remove);
			$copy_message = array_merge($copy_message,array_values($copy_result));

			if (! $copy_result)
				$hadError = true;
		}

		if (! $hadError && $remove) {
			$delete_result = $serverSRC->delete($dnSRC);

			if ($delete_result)
				array_push($copy_message,sprintf('%s %s: <b>%s</b> %s',_('Delete successful'),_('DN'),$dnDST,_('has been deleted.')));
		}

	} else {
		$copy_result = copy_dn($serverSRC,$serverDST,$dnSRC,$dnDST,$remove);

		if ($copy_result)
			array_push($copy_message,sprintf('%s %s: <b>%s</b> %s',
				$remove ? _('Move successful') : _('Copy successful'),
				_('DN'),$dnDST,_('has been created.')));
		else
			array_push($copy_message,sprintf('%s %s: <b>%s</b> %s',
				$remove ? _('Move NOT successful') : _('Copy NOT successful'),
				_('DN'),$dnDST,_('has NOT been created.')));
	}

	return $copy_message;
}

function copy_dn($serverSRC,$serverDST,$dnSRC,$dnDST,$remove) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	$request = array();
	$request['pageSRC'] = new PageRender($serverSRC->getIndex(),get_request('template','REQUEST',false,'none'));
	$request['pageSRC']->setDN($dnSRC);
	$request['pageSRC']->accept();

	$request['pageDST'] = new PageRender($serverDST->getIndex(),get_request('template','REQUEST',false,'none'));
	$request['pageDST']->setContainer($serverDST->getContainer($dnDST));
	$request['pageDST']->accept();

	$request['templateSRC'] = $request['pageSRC']->getTemplate();
	$request['templateDST'] = $request['pageDST']->getTemplate();
	$request['templateDST']->copy($request['pageSRC']->getTemplate(),get_rdn($dnDST,0));

	# Create of move the entry
	if ($remove)
		return $serverDST->rename($request['templateSRC']->getDN(),$request['templateDST']->getRDN(),$serverDST->getContainer($dnDST),true);
	else
		return $serverDST->add($request['templateDST']->getDN(),$request['templateDST']->getLDAPadd());
}

function build_tree($server,$dn,$buildtree) {
	if (DEBUG_ENABLED && (($fargs=func_get_args())||$fargs='NOARGS'))
		debug_log('Entered (%%)',1,0,__FILE__,__LINE__,__METHOD__,$fargs);

	# We search all children, not only the visible children in the tree
	$children = $server->getContainerContents($dn,null,0);

	if (count($children)) {
		$buildtree[$dn] = $children;
		foreach ($children as $child_dn)
			$buildtree = build_tree($server,$child_dn,$buildtree);
	}

	if (DEBUG_ENABLED)
		debug_log('Returning (%s)',1,0,__FILE__,__LINE__,__METHOD__,$buildtree);

	return $buildtree;
}
?>
