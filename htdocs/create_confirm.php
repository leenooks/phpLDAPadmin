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

$request = array();
$request['redirect'] = get_request('redirect','POST',false,false);

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setContainer(get_request('container','REQUEST',true));
$request['page']->accept();
$request['template'] = $request['page']->getTemplate();

if (! $request['template']->getContainer() || ! $app['server']->dnExists($request['template']->getContainer()))
	error(sprintf(_('The container you specified (%s) does not exist. Please try again.'),$request['template']->getContainer()),'error','index.php');

# Check if the container is a leaf - we shouldnt really return a hit here, the template engine shouldnt have allowed a user to attempt to create an entry...
$tree = get_cached_item($app['server']->getIndex(),'tree');

$request['container'] = $tree->getEntry($request['template']->getContainer());
if (! $request['container']) {
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

# Check for unique attributes
$app['server']->checkUniqueAttrs($request['template']->getDN(),$request['template']->getLDAPadd());

$request['page']->drawTitle(_('Create LDAP Entry'));
$request['page']->drawSubTitle(sprintf('%s: <b>%s</b>&nbsp;&nbsp;&nbsp;%s: <b>%s</b>',
	_('Server'),$app['server']->getName(),_('Container'),$request['template']->getContainer()));

# Confirm the creation
if (count($request['template']->getLDAPadd(true))) {
	echo '<div style="text-align: center;">';
	echo _('Do you want to create this entry?');
	echo '<br /><br />';
	echo '</div>';

	echo "\n\n";
	echo '<form action="cmd.php" method="post" id="create_form">';
	echo '<div>';
	echo '<input type="hidden" name="cmd" value="create" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="container" value="%s" />',$request['template']->getContainerEncode(false));
	printf('<input type="hidden" name="template" value="%s" />',$request['template']->getID());
	foreach ($request['template']->getRDNAttrs() as $rdn)
		printf('<input type="hidden" name="rdn_attribute[]" value="%s" />',htmlspecialchars($rdn));
	echo "\n";

	$request['page']->drawHiddenAttributes();
	echo '</div>';

	echo '<table class="result_table" style="margin-left: auto; margin-right: auto;">';
	echo "\n";

	printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td></tr>',
		_('Attribute'),_('New Value'),_('Skip'));
	echo "\n\n";

	$counter = 0;
	printf('<tr class="%s"><td colspan="3" style="text-align: center;"><b>%s</b></td></tr>',$counter%2 ? 'even' : 'odd',htmlspecialchars($request['template']->getDN()));

	foreach ($request['template']->getLDAPadd(true) as $attribute) {
		$counter++;

		printf('<tr class="%s">',$counter%2 ? 'even' : 'odd');
		printf('<td><b>%s</b></td>',$attribute->getFriendlyName());

		# Show NEW Values
		echo '<td><span style="white-space: nowrap;">';
		$request['page']->draw('CurrentValues',$attribute);
		echo '</span></td>';

		# Show SKIP Option
		$input_disabled = '';
		$input_onclick = '';

		if ($attribute->isRequired())
			$input_disabled = 'disabled="disabled"';

		printf('<td><input name="skip_array[%s]" id="skip_array_%s" type="checkbox" %s %s/></td>',
			htmlspecialchars($attribute->getName()),htmlspecialchars($attribute->getName()),$input_disabled,$input_onclick);
		echo '</tr>';
		echo "\n\n";
	}

	echo '</table>';

	echo '<div style="text-align: center;">';
	echo '<br />';

	printf('<input type="submit" value="%s" %s/>',
		_('Commit'),
		(isAjaxEnabled() ? sprintf('onclick="return ajSUBMIT(\'BODY\',document.getElementById(\'create_form\'),\'%s\');"',_('Updating Object')) : ''));

	printf('<input type="submit" name="cancel" value="%s" %s/>',
		_('Cancel'),
		(isAjaxEnabled() ? sprintf('onclick="return ajDISPLAY(\'BODY\',\'cmd=template_engine&server_id=%s&container=%s\',\'%s\');"',$app['server']->getIndex(),$request['template']->getContainer(),_('Retrieving DN')) : ''));

	echo '</div>';
	echo '</form>';
	echo '<br />';

} else {
	$href = sprintf('cmd=template_engine&server_id=%s&dn=%s',
		$app['server']->getIndex(),$request['template']->getDNEncode());

	echo '<div style="text-align: center;">';
	echo _('You made no changes');

	if (isAjaxEnabled())
		printf(' <a href="cmd.php?%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');">%s</a>.',
			htmlspecialchars($href),htmlspecialchars($href),_('Retrieving DN'),_('Go back'));
	else
		printf(' <a href="cmd.php?%s">%s</a>.',htmlspecialchars($href),_('Go back'));

	echo '</div>';
}
?>
