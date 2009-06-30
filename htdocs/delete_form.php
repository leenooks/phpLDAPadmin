<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/delete_form.php,v 1.20.4.6 2006/04/29 04:05:14 wurley Exp $

/**
 * delete_form.php
 * Displays a last chance confirmation form to delete a dn.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

if ($ldapserver->isReadOnly())
	pla_error(_('You cannot perform updates while server is in read-only mode'));
if (! $ldapserver->haveAuthInfo())
	pla_error(_('Not enough information to login to server. Please check your configuration.'));

$dn = $_GET['dn'];
$children = $ldapserver->getContainerContents($dn,0,'(objectClass=*)',LDAP_DEREF_NEVER);
$has_children = count($children) > 0 ? true : false;

include './header.php';

echo '<body>';
printf('<h3 class="title">'._('Delete %s').'</h3>',htmlspecialchars(get_rdn($dn)));
printf('<h3 class="subtitle">%s: <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));
echo "\n";

echo '<center>';

if ($has_children) {
	printf('<b>%s</b><br /><br />',_('Permanently delete all children also?'));
	flush();

	# get the total number of child objects (whole sub-tree)
	$s = $ldapserver->search(null,dn_escape($dn),'objectClass=*',array('dn'));
	$sub_tree_count = count($s);

	echo '<table class="delete_confirm">';
	echo '<tr>';
	echo '<td><p>';
	printf(_('This entry is the root of a sub-tree containing %s entries.'),$sub_tree_count);
	printf('<small>(<a href="search.php?search=true&amp;server_id=%s&amp;filter=%s&amp;base_dn=%s&amp;form=advanced&amp;scope=sub">%s</a>)</small>',
		$ldapserver->server_id,rawurlencode('objectClass=*'),rawurlencode($dn),_('view entries'));
	echo '<br /><br />';

	printf(_('phpLDAPadmin can recursively delete this entry and all %s of its children. See below for a list of all the entries that this action will delete. Do you want to do this?'),($sub_tree_count-1));
	echo '<br /><br />';

	printf('<small>%s</small>',
		_('Note: this is potentially very dangerous and you do this at your own risk. This operation cannot be undone. Take into consideration aliases, referrals, and other things that may cause problems.'));
	echo '<br /><br />';
	echo "\n";

	echo '<table width="100%">';
	echo '<tr>';
	echo '<td><center>';
	echo '<form action="rdelete.php" method="post">';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" class="scary" value="%s" />',sprintf(_('Delete all %s objects'),$sub_tree_count));
	echo '</form>';
	echo '</center></td>';

	echo '<td><center>';
	echo '<form action="template_engine.php" method="get">';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" name="submit" value="%s" class="cancel" />',_('Cancel'));
	echo '</form>';
	echo '</center></td>';
	echo '</tr>';
	echo '</table>';
	echo "\n";

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo "\n";

	flush();

	echo '<br /><br />';
	echo _('List of entries to be deleted:');
	echo '<br />';

	printf('<select size="%s" multiple disabled style="background:white; color:black;width:500px" >',min(10,$sub_tree_count));
	$i=0;
	foreach ($s as $dn => $junk) {
		$i++;
		printf('<option>%s. %s</option>',$i,htmlspecialchars(dn_unescape($dn)));
	}
	echo '</select>';
	echo "\n";

} else {
	echo '<table class="delete_confirm">';
	echo '<tr>';

	echo '<td nowrap>';
	echo _('Are you sure you want to permanently delete this object?');
	echo '<br /><br />';

	printf('<acronym title="%s">%s</acronym>: <b>%s</b>',_('Distinguished Name'),_('DN'),pretty_print_dn($dn));
	echo '<br />';
	printf('%s: <b>%s</b>',_('Server'),htmlspecialchars($ldapserver->name));
	echo '<br /><br />';
	echo "\n";

	echo '<table width="100%">';
	echo '<tr>';

	echo '<td><center>';
	echo '<form action="delete.php" method="post">';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" name="submit" value="%s" class="scary" />',_('Delete'));
	echo '</form>';

	echo '</center></td>';

	echo '<td><center>';
	echo '<form action="template_engine.php" method="get">';
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($dn));
	printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
	printf('<input type="submit" name="submit" value="%s" class="cancel" />',_('Cancel'));
	echo '</form>';

	echo '</center></td>';
	echo '</tr>';
	echo '</table>';
	echo "\n";

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo "\n";

}

echo '</center>';
echo '<br />';
echo '</body>';
echo '</html>';
?>
