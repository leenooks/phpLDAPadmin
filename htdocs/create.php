<?php
/**
 * Creates a new object in LDAP.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# If cancel was selected, we'll redirect
if (get_request('cancel','REQUEST')) {
	header('Location: index.php');
	die();
}

$request = array();
$request['redirect'] = get_request('redirect','POST',false,false);

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setContainer(get_request('container','REQUEST',true));
$request['page']->accept();
$request['template'] = $request['page']->getTemplate();

if ((! $request['template']->getContainer() || ! $app['server']->dnExists($request['template']->getContainer())) && ! get_request('create_base'))
	error(sprintf(_('The container you specified (%s) does not exist. Please try again.'),$request['template']->getContainer()),'error','index.php');

# Check if the container is a leaf - we shouldnt really return a hit here, the template engine shouldnt have allowed a user to attempt to create an entry...
$tree = get_cached_item($app['server']->getIndex(),'tree');

$request['container'] = $tree->getEntry($request['template']->getContainer());
if (! $request['container'] && ! get_request('create_base')) {
	$tree->addEntry($request['template']->getContainer());
	$request['container'] = $tree->getEntry($request['template']->getContainer());
}

# Check our RDN
if (! count($request['template']->getRDNAttrs()))
	error(_('The were no attributes marked as an RDN attribute.'),'error','index.php');
if (! $request['template']->getRDN())
	error(_('The RDN field is empty?'),'error','index.php');

# Some other attribute checking...
foreach ($request['template']->getAttributes() as $attribute) {
	# Check that our Required Attributes have a value - we shouldnt really return a hit here, the template engine shouldnt have allowed this to slip through.
	# @todo this isIgnoredAttr() function is missing?
	if ($attribute->isRequired() && ! count($attribute->getValues()) && ! $app['server']->isIgnoredAttr($attr->getName()))
		error(sprintf(_('You left the value blank for required attribute (%s).'),
			$attribute->getName(false)),'error','index.php');
}

# Create the entry
$add_result = $app['server']->add($request['template']->getDN(),$request['template']->getLDAPadd());

if ($add_result) {
	$action_number = $_SESSION[APPCONFIG]->getValue('appearance','action_after_creation');
	$href = sprintf('cmd=template_engine&server_id=%s',$app['server']->getIndex());

	if ($request['redirect'])
		$redirect_url = $request['redirect'];

	else if ($action_number == 2)
		$redirect_url = sprintf('cmd.php?%s&template=%s&container=%s',
			$href,'default',$request['template']->getContainerEncode());

	else
		$redirect_url = sprintf('cmd.php?%s&template=%s&dn=%s',
			$href,'default',$request['template']->getDNEncode());

	if ($action_number == 1 || $action_number == 2)
		printf('<meta http-equiv="refresh" content="0; url=%s" />',$redirect_url);

	if ($action_number == 1 || $action_number == 2) {
		$create_message = sprintf('%s %s: <b>%s</b> %s',
			_('Creation successful!'),_('DN'),$request['template']->getDN(),_('has been created.'));

		if (isAjaxEnabled())
			$redirect_url .= sprintf('&refresh=SID_%s_nodes&noheader=1',$app['server']->getIndex());

		system_message(array(
			'title'=>_('Create Entry'),
			'body'=>$create_message,
			'type'=>'info'),
			$redirect_url);

	} else {
		$request['page']->drawTitle(_('Entry created'));
		$request['page']->drawSubTitle(sprintf('%s: <b>%s</b>&nbsp;&nbsp;&nbsp;%s: <b>%s</b>',
			_('Server'),$app['server']->getName(),_('Distinguished Name'),$request['template']->getDN()));

		echo '<br />';
		echo '<center>';
		printf('<a href="cmd.php?%s&amp;dn=%s">%s</a>.',
			htmlspecialchars($href),rawurlencode($request['template']->getDN()),_('Display the new created entry'));
		echo '<br />';
		printf('<a href="cmd.php?%s&amp;container=%s">%s</a>.',
			htmlspecialchars($href),rawurlencode($request['template']->getContainer()),_('Create another entry'));
		echo '</center>';
	}
}
?>
