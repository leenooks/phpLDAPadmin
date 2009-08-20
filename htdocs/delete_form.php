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
$request['dn'] = get_request('dn','GET');

# Check if the entry exists.
if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	system_message(array(
		'title'=>_('Entry does not exist'),
		'body'=>sprintf('%s (%s)',_('The entry does not exist'),$request['dn']),
		'type'=>'error'),'index.php');

# We search all children, not only the visible children in the tree
$request['children'] = $app['server']->getContainerContents($request['dn'],null,0,'(objectClass=*)',LDAP_DEREF_NEVER);

printf('<h3 class="title">%s %s</h3>',_('Delete'),get_rdn($request['dn']));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$app['server']->getName(),_('Distinguished Name'),$request['dn']);
echo "\n";

echo '<center>';

if (count($request['children'])) {
	printf('<b>%s</b><br /><br />',_('Permanently delete all children also?'));

	$search['href'] = htmlspecialchars(sprintf('cmd.php?cmd=query_engine&server_id=%s&filter=%s&base=%s&scope=sub&query=none&format=list',
		$app['server']->getIndex(),rawurlencode('objectClass=*'),rawurlencode($request['dn'])));

	$query = array();
	$query['base'] = $request['dn'];
	$query['scope'] = 'sub';
	$query['attrs'] = array('dn');
	$query['size_limit'] = 0;
	$query['deref'] = LDAP_DEREF_NEVER;
	$request['search'] = $app['server']->query($query,null);

	echo '<table class="forminput" border=0>';
	echo '<tr>';
	echo '<td colspan=2>';
	printf(_('This entry is the root of a sub-tree containing %s entries.'),count($request['search']));
	printf(' <small>(<a href="%s">%s</a>)</small>',
		$search['href'],_('view entries'));
	echo '</td></tr>';

	echo '<tr><td colspan=2>&nbsp;</td></tr>';

	printf('<tr><td colspan=2>%s</td></tr>',
		sprintf(_('phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?'),count($request['search'])));

	echo '<tr><td colspan=2>&nbsp;</td></tr>';

	printf('<tr><td colspan=2><small>%s</small></td></tr>',
		_('Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.'));
	echo "\n";

	echo '<tr>';
	echo '<td width=50%><center>';
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="rdelete" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	printf('<input type="submit" value="%s" />',sprintf(_('Delete all %s objects'),count($request['search'])));
	echo '</form>';
	echo '</center></td>';

	echo '<td width=50%><center>';
	echo '<form action="cmd.php" method="get">';
	echo '<input type="hidden" name="cmd" value="template_engine" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	printf('<input type="submit" name="submit" value="%s" />',_('Cancel'));
	echo '</form>';
	echo '</center></td>';
	echo '</tr>';
	echo "\n";

	echo '</table>';
	echo "\n";

	echo '<br /><br />';
	echo _('List of entries to be deleted:');
	echo '<br />';

	$i = 0;
	printf('<select size="%s" multiple disabled style="background:white; color:black;width:500px" >',min(10,count($request['search'])));
	foreach ($request['search'] as $key => $value)
		printf('<option>%s. %s</option>',++$i,dn_unescape($value['dn']));
	echo '</select>';
	echo "\n";

} else {
	echo '<table class="forminput" border=0>';

	printf('<tr><td colspan=4>%s</td></tr>',_('Are you sure you want to permanently delete this object?'));
	echo '<tr><td colspan=4>&nbsp;</td></tr>';

	printf('<tr><td width=10%%>%s:</td><td colspan=3 width=75%%><b>%s</b></td></tr>',_('Server'),$app['server']->getName());
	printf('<tr><td width=10%%><acronym title="%s">%s</acronym></td><td colspan=3 width=75%%><b>%s</b></td></tr>',
		_('Distinguished Name'),_('DN'),$request['dn']);
	echo '<tr><td colspan=4>&nbsp;</td></tr>';
	echo "\n";

	echo '<tr>';
	echo '<td colspan=2 width=50%><center>';
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="delete" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	printf('<input type="submit" name="submit" value="%s" />',_('Delete'));
	echo '</form>';

	echo '</center></td>';
	echo '<td colspan=2 width=50%><center>';

	echo '<form action="cmd.php" method="get">';
	echo '<input type="hidden" name="cmd" value="template_engine" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	printf('<input type="submit" name="submit" value="%s" />',_('Cancel'));
	echo '</form>';

	echo '</center></td>';
	echo '</tr>';
	echo '</table>';
	echo "\n";
}

echo '</center>';
echo '<br />';
?>
