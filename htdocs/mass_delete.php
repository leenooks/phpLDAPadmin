<?php
/**
 * Displays a last chance confirmation form to delete a DN.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# The DN we are working with
$request = array();
$request['dn'] = get_request('dn','REQUEST');

if (! $request['dn'])
	system_message(array(
		'title'=>_('No entry selected'),
		'body'=>_('No entry was selected to delete'),
		'type'=>'warn'),'index.php');

if (! is_array($request['dn']))
	$request['dn'] = array($request['dn']);

$request['children'] = array();
$request['parent'] = array();
foreach ($request['dn'] as $dn) {
	# Check if the entry exists.
	if (! $dn || ! $app['server']->dnExists($dn))
		system_message(array(
			'title'=>_('Entry does not exist'),
			'body'=>sprintf('%s (%s/%s)',_('The entry does not exist and will be ignored'),$dn),
			'type'=>'error'));

	# We search all children, not only the visible children in the tree
	if (! in_array_ignore_case($dn,$request['children'])) {
		$request['children'] = array_merge($request['children'],$app['server']->getContainerContents($dn,null,0,'(objectClass=*)',LDAP_DEREF_NEVER));
		array_push($request['parent'],$dn);
	}
}

printf('<h3 class="title">%s</h3>',_('Mass Delete'));
printf('<h3 class="subtitle">%s: <b>%s</b></h3>',_('Server'),$app['server']->getName());
echo "\n";

echo '<center>';
echo '<table class="forminput" border="0">';

if (count($request['parent']) == 1)
	printf('<tr><td colspan="4"><b>%s</b></td></tr>',_('Are you sure you want to permanently delete this object?'));
else
	printf('<tr><td colspan="4"><b>%s</b></td></tr>',_('Are you sure you want to permanently delete these objects?'));

echo '<tr><td colspan="4">&nbsp;</td></tr>';
printf('<tr><td style="width: 10%%;">%s:</td><td colspan="3" style="width: 75%%;"><b>%s</b></td></tr>',_('Server'),$app['server']->getName());

foreach ($request['parent'] as $dn)
	printf('<tr><td style="width: 10%%;"><acronym title="%s">%s</acronym></td><td colspan="3" style="width: 75%%;"><b>%s</b></td></tr>',
		_('Distinguished Name'),_('DN'),$dn);

echo '<tr><td colspan="4">&nbsp;</td></tr>';

$request['delete'] = $request['parent'];

if (count($request['children'])) {
	printf('<tr><td colspan="4"><b>%s</b></td></tr>',_('Permanently delete all children also?'));
	echo '<tr><td colspan="4">&nbsp;</td></tr>';

	# We need to see if the children have children
	$query = array();
	$query['scope'] = 'sub';
	$query['attrs'] = array('dn');
	$query['size_limit'] = 0;
	$query['deref'] = LDAP_DEREF_NEVER;

	$request['search'] = array();
	foreach ($request['children'] as $dn) {
		$query['base'] = $dn;
		$request['search'] = array_merge($request['search'],$app['server']->query($query,null));
	}

	foreach ($request['search'] as $value)
		array_push($request['delete'],$value['dn']);

	echo '<tr>';
	echo '<td colspan="4">';
	printf(_('This request also includes %s children entries.'),count($request['children']));
	echo '</td></tr>';

	printf('<tr><td colspan="4">%s</td></tr>',
		sprintf(_('phpLDAPadmin can also recursively delete all %s of the child entries. See below for a list of all the entries that this action will delete. Do you want to do this?'),count($request['children'])));

	echo '<tr><td colspan="4">&nbsp;</td></tr>';

	printf('<tr><td colspan="4"><small>%s</small></td></tr>',
		_('Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.'));
	echo "\n";

	echo '<tr><td colspan="4">&nbsp;</td></tr>';

	echo "\n";

	printf('<tr><td colspan="4"><center><b>%s</b></center></td></tr>',_('List of entries to be deleted:'));
	echo '<tr><td colspan="4">&nbsp;</td></tr>';

	$i = 0;
	echo '<tr><td colspan="4"><center>';
	printf('<select size="%s" multiple disabled style="background:white; color:black;width:500px" >',min(10,count($request['delete'])));
	foreach ($request['delete'] as $key => $value)
		printf('<option>%s. %s</option>',++$i,htmlspecialchars(dn_unescape($value)));
	echo '</select>';
	echo '</center></td></tr>';
	echo "\n";

	echo '<tr><td colspan="4">&nbsp;</td></tr>';
}

echo '<tr>';
echo '<td colspan="2" style="width: 50%; text-align: center;">';
echo '<form action="cmd.php" method="post">';
echo '<input type="hidden" name="cmd" value="rdelete" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
foreach ($request['parent'] as $dn)
	printf('<input type="hidden" name="dn[]" value="%s" />',htmlspecialchars($dn));
printf('<input type="submit" value="%s" />',sprintf(_('Delete all %s objects'),count($request['delete'])));
echo '</form>';
echo '</center></td>';

echo '<td colspan="2" style="width: 50%; text-align: center;">';

echo '<form action="cmd.php" method="get">';
echo '<input type="hidden" name="cmd" value="template_engine" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
printf('<input type="submit" name="submit" value="%s" />',_('Cancel'));
echo '</form>';

echo '</center></td>';
echo '</tr>';
echo "\n";

echo '</table>';
echo '</center>';

echo '<br />';
?>
