<?php
/**
 * This page will allow the adding of additional ObjectClasses to an item.
 * + If the ObjectClass to be added requires additional MUST attributes to be
 *   defined, then they will be prompted for.
 * + If the ObjectClass doesnt need any additional MUST attributes, then it
 *   will be silently added to the object.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN and OBJECTCLASS we are working with.
$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

# Check if the entry exists.
if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$request['page'] = new TemplateRender($app['server']->getIndex(),get_request('template','REQUEST',false,null));
$request['page']->setDN($request['dn']);
$request['page']->accept(true);
$request['template'] = $request['page']->getTemplate();

$attribute_factory = new AttributeFactory();

# Grab the required attributes for the new objectClass
$ldap = array();
$ldap['attrs']['must'] = array();

foreach ($request['template']->getAttribute('objectclass')->getValues() as $oclass_name) {
	# Exclude "top" if its there.
	if (! strcasecmp('top',$oclass_name))
		continue;

	if ($soc = $app['server']->getSchemaObjectClass($oclass_name))
		$ldap['attrs']['must'] = array_merge($ldap['attrs']['must'],$soc->getMustAttrNames(true));
}

$ldap['attrs']['must'] = array_unique($ldap['attrs']['must']);

/* Build a list of the attributes that this new objectClass requires,
 * but that the object does not currently contain */
$ldap['attrs']['need'] = array();
foreach ($ldap['attrs']['must'] as $attr)
	if (is_null($request['template']->getAttribute($attr)))
		array_push($ldap['attrs']['need'],$attribute_factory->newAttribute($attr,array('values'=>array()),$app['server']->getIndex()));

# Mark all the need attributes as shown
foreach ($ldap['attrs']['need'] as $index => $values)
	$ldap['attrs']['need'][$index]->show();

if (count($ldap['attrs']['need']) > 0) {
	$request['page']->drawTitle(sprintf('%s <b>%s</b>',_('Add new objectClass to'),get_rdn($request['dn'])));
	$request['page']->drawSubTitle();

	echo '<div style="text-align: center">';
	printf('<small><b>%s: </b>%s <b>%s</b> %s %s</small>',
		_('Instructions'),
		_('In order to add these objectClass(es) to this entry, you must specify'),
		count($ldap['attrs']['need']),_('new attributes'),
		_('that this objectClass requires.'));

	echo '<br /><br />';

	echo '<form action="cmd.php" method="post" id="entry_form">';
	echo '<div>';

	if ($_SESSION[APPCONFIG]->getValue('confirm','update'))
		echo '<input type="hidden" name="cmd" value="update_confirm" />';
	else
		echo '<input type="hidden" name="cmd" value="update" />';

	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	echo '</div>';

	echo '<table class="entry" cellspacing="0" border="0" style="margin-left: auto; margin-right: auto;">';
	printf('<tr><th colspan="2">%s</th></tr>',_('New Required Attributes'));

	$counter = 0;
	echo '<tr><td colspan="2">';
	foreach ($request['template']->getAttribute('objectclass')->getValues() as $value)
		$request['page']->draw('HiddenValue',$request['template']->getAttribute('objectclass'),$counter++);
	echo '</td></tr>';

	foreach ($ldap['attrs']['need'] as $count => $attr)
		$request['page']->draw('Template',$attr);

	echo '</table>';

	printf('<div style="text-align: center;"><br /><input type="submit" value="%s" /></div>',_('Add ObjectClass and Attributes'));
	echo '</form>';
	echo '</div>';

# There are no other required attributes, so we just need to add the objectclass to the DN.
} else {
	$result = $app['server']->modify($request['dn'],$request['template']->getLDAPmodify());

	if ($result) {
		$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s&modified_attrs[]=objectclass',
			$app['server']->getIndex(),rawurlencode($request['dn']));

		if (get_request('meth','REQUEST') == 'ajax')
			$href .= '&meth=ajax';

		header(sprintf('Location: %s',$href));
		die();
	}
}
?>
