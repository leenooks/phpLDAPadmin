<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/modify_member_form.php,v 1.4 2006/10/29 01:47:08 wurley Exp $

/**
 * Displays a form to allow the user to modify group members.
 *
 * Variables that come in via common.php
 *  - server_id
 * Variables that come in as GET vars:
 *  - dn (rawurlencoded)
 *  - attr (rawurlencoded) the attribute to which we are adding a value
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

$attr = $_GET['attr'];
$dn = isset($_GET['dn']) ? $_GET['dn'] : null;
$encoded_dn = rawurlencode($dn);
$encoded_attr = rawurlencode($attr);

if (! is_null($dn))
	$rdn = get_rdn($dn);
else
	$rdn = null;

# Get all current group members
$current_members = $ldapserver->getDNAttr($dn,$attr);
if ($current_members)
	$num_current_members = (is_array($current_members) ? count($current_members) : 1);
else
	$num_current_members = 0;

/*
 * If there is only one member, convert scalar to array,
 * arrays are required later when processing members
 */
if ($num_current_members == 1)
	$current_members = array($current_members);

sort($current_members);

# Loop through all base dn's and search possible member entries
foreach ($ldapserver->getBaseDN() as $base_dn) {

	# Get all entries that can be added to the group
	if (preg_match("/^$attr$/i",$config->GetValue('modify_member','posixgroupattr')))
		$possible_values = array_merge($ldapserver->search(null,$base_dn,
			$config->GetValue('modify_member','posixfilter'),array($config->GetValue('modify_member','posixattr'))));
	else
		$possible_values = array_merge($ldapserver->search(null,$base_dn,
			$config->GetValue('modify_member','filter'),array($config->GetValue('modify_member','attr'))));
}

if ($possible_values)
	$num_possible_values = (is_array($possible_values) ? count($possible_values) : 1);
else
	$num_possible_values = 0;

sort($possible_values);

include './header.php';

echo '<body>';
printf('<h3 class="title">%s <b>%s</b></h3>',_('Modify group'),htmlspecialchars($rdn));

printf('<h3 class="subtitle">%s <b>%s</b> &nbsp;&nbsp;&nbsp; %s: <b>%s</b></h3>',
	_('Server'),$ldapserver->name,_('Distinguished Name'),htmlspecialchars($dn));

printf('%s <b>%s</b> %s <b>%s</b>:',
	_('There are'),$num_current_members,_('members in group'),htmlspecialchars($rdn));

for ($i=0; $i<count($possible_values); $i++) {
	if (preg_match("/^$attr$/i",$config->GetValue('modify_member','posixgroupattr')))
		$possible_members[$i] = $possible_values[$i][$config->GetValue('modify_member','posixattr')];
	else
		$possible_members[$i] = $possible_values[$i][$config->GetValue('modify_member','attr')];
}

sort($possible_members);

/*
 * Show only user that are not already in group.
 * This loop removes existing users from possible members
 */
foreach ($possible_members as $pkey => $possible) {
	foreach ($current_members as $current) {
		if (preg_match("/^$current$/i","$possible_members[$pkey]")) {
			unset($possible_members[$pkey]);
			break;
		}
	}
}

/*
 * Draw form with select boxes, left for all possible members and
 * right one for those that belong to group
 */

# Modifications will be sent to update_confirm which takes care of rest of the processing
echo '<br />';
echo '<br />';
echo '<form action="update_confirm.php" method="post" class="add_value" name="member">';

echo '<table class="modify_members">';

echo '<tr>';
printf('<td><img src="images/user.png" alt="Users" /> %s</td>',_('Available members'));
printf('<td><img src="images/uniquegroup.png" alt="Members" /> %s</td>',_('Group members'));
echo '</tr>';

# Generate select box from all possible members
echo '<tr>';
echo '<td>';
echo '<select name="notmembers" size="10" multiple>';

foreach ($possible_members as $possible)
	printf('<option>%s</option>',$possible);

echo '</select>';
echo '</td>';

# Generate select box from all current members
echo '<td>';
echo '<select name="members" size="10" multiple>';

foreach ($current_members as $current)
	printf('<option>%s</option>',$current);

echo '</select>';
echo '</td>';

echo '</tr>';

# Show buttons which move users from left to right and vice versa
echo '<tr>';
echo '<td>';
printf('<input type="button" onClick="one2two()" value="%s >>" />&nbsp;<input type="button" onClick="all2two()" value="%s >>" />',
	_('Add selected'),_('Add all'));
echo '</td>';
echo '<td>';
printf('<input type="button" onClick="two2one()" value="<< %s" />&nbsp;<input type="button" onClick="all2one()" value="<< %s" />',
	_('Remove selected'),('Remove all'));
echo '</td>';
echo '</tr>';

echo '<tr><td colspan="2">';

# Hidden attributes for update_confirm.php
printf('<input type="hidden" name="server_id" value="%s" />',$ldapserver->server_id);
printf('<input type="hidden" name="dn" value="%s" />',$dn);
printf('<input type="hidden" name="attr" value="%s" />',$encoded_attr);

/*
 * Generate array of input text boxes from current members.
 * update_confirm.php will see this as old_values[member-attribute][item]
 */
for ($i=0; $i<$num_current_members; $i++)
	printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',
		htmlspecialchars($attr),$i,htmlspecialchars($current_members[$i]));

/*
 * Javascript generates array of input text boxes from new members.
 * update_confirm.php will see this as new_values[member-attribute][item]
 * Input text boxes will be generated to div=dnu
 */
echo '<div id="dnu">';
printf('<input type="hidden" name="new_values[%s][]" value="" />',htmlspecialchars($attr));
echo '</div>';

# Submit values to update_confirm.php and when clicked, run addSelected
printf('<input type="submit" name="save" value="%s" onClick="update_new_values(\'%s\',\'modifymember\')" />',_('Save changes'),$attr);
echo '</td></tr>';

echo '</table>';
echo '</form>';

# Variables for Javascript function that moves members from left to right
echo '<script type="text/javascript" language="javascript">';
echo 'var m1 = document.member.notmembers;';
echo 'var m2 = document.member.members;';
echo '</script>';

echo '</body></html>';
?>
