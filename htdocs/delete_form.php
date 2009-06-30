<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_form.php,v 1.26.2.2 2008/12/12 12:20:22 wurley Exp $

/**
 * delete_form.php
 * Displays a last chance confirmation form to delete a dn.
 *
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	error(_('You cannot perform updates while server is in read-only mode'),'error','index.php');

$entry = array();
$entry['dn']['string'] = get_request('dn','GET');
$entry['dn']['html'] = htmlspecialchars($entry['dn']['string']);

# We search all children, not only the visible children in the tree
$entry['children'] = $ldapserver->getContainerContents($entry['dn']['string'],0,'(objectClass=*)',LDAP_DEREF_NEVER);

printf('<h3 class="title">'._('Delete %s').'</h3>',htmlspecialchars(get_rdn($entry['dn']['string'])));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),$entry['dn']['html']);
echo "\n";

echo '<center>';

if (count($entry['children'])) {
	printf('<b>%s</b><br /><br />',_('Permanently delete all children also?'));

	# Get the total number of child objects (whole sub-tree)
	$search['entries'] = $ldapserver->search(null,dn_escape($entry['dn']['string']),'objectClass=*',array('dn'));
	$search['count'] = count($search['entries']);
	$search['href'] = htmlspecialchars(sprintf('cmd.php?cmd=search&search=true&;server_id=%s&filter=%s&base_dn=%s&form=advanced&scope=sub',
		$ldapserver->server_id,rawurlencode('objectClass=*'),rawurlencode($entry['dn']['string'])));

	echo '<table class="forminput" border=0>';
	echo '<tr>';
	echo '<td colspan=2>';
	printf(_('This entry is the root of a sub-tree containing %s entries.'),$search['count']);
	printf(' <small>(<a href="%s">%s</a>)</small>',
		$search['href'],_('view entries'));
	echo '</td></tr>';

	echo '<tr><td colspan=2>&nbsp;</td></tr>';

	printf('<tr><td colspan=2>%s</td></tr>',
		sprintf(_('phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?'),$search['count']-1));

	echo '<tr><td colspan=2>&nbsp;</td></tr>';

	printf('<tr><td colspan=2><small>%s</small></td></tr>',
		_('Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.'));
	echo "\n";

	echo '<tr>';
	echo '<td width=50%><center>';
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="rdelete" />';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry['dn']['string']));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" value="%s" />',sprintf(_('Delete all %s objects'),$search['count']));
	echo '</form>';
	echo '</center></td>';

	echo '<td width=50%><center>';
	echo '<form action="cmd.php" method="get">';
	echo '<input type="hidden" name="cmd" value="template_engine" />';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry['dn']['string']));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
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

	printf('<select size="%s" multiple disabled style="background:white; color:black;width:500px" >',min(10,$search['count']));
	$i = 0;
	foreach ($search['entries'] as $dn => $junk) {
		$i++;
		printf('<option>%s. %s</option>',$i,htmlspecialchars(dn_unescape($dn)));
	}
	echo '</select>';
	echo "\n";

} else {
	echo '<table class="forminput" border=0>';

	printf('<tr><td colspan=4>%s</td></tr>',_('Are you sure you want to permanently delete this object?'));
	echo '<tr><td colspan=4>&nbsp;</td></tr>';

	printf('<tr><td width=10%%>%s:</td><td colspan=3 width=75%%><b>%s</b></td></tr>',_('Server'),htmlspecialchars($ldapserver->name));
	printf('<tr><td width=10%%><acronym title="%s">%s</acronym></td><td colspan=3 width=75%%><b>%s</b></td></tr>',
		_('Distinguished Name'),_('DN'),$entry['dn']['string']);
	echo '<tr><td colspan=4>&nbsp;</td></tr>';
	echo "\n";

	echo '<tr>';
	echo '<td colspan=2 width=50%><center>';
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="delete" />';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry['dn']['string']));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" name="submit" value="%s" />',_('Delete'));
	echo '</form>';

	echo '</center></td>';
	echo '<td colspan=2 width=50%><center>';

	echo '<form action="cmd.php" method="get">';
	echo '<input type="hidden" name="cmd" value="template_engine" />';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($entry['dn']['string']));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
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
