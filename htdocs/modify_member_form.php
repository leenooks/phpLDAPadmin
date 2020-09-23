<?php
/**
 * Displays a form to allow the user to modify group members.
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
$request['attr'] = get_request('attr','GET');

$request['page'] = new TemplateRender($app['server']->getIndex(),get_request('template','REQUEST',false,null));
$request['page']->setDN($request['dn']);
$request['page']->accept(true);
$request['template'] = $request['page']->getTemplate();

if (! is_null($request['dn']))
	$rdn = get_rdn($request['dn']);
else
	$rdn = null;

# Get all current group members
$current_members = $app['server']->getDNAttrValue($request['dn'],$request['attr']);
usort($current_members,'pla_compare_dns');

# Loop through all base dn's and search possible member entries
$query = array();

# Get all entries that can be added to the group
if (preg_match("/^".$request['attr']."$/i",$_SESSION[APPCONFIG]->getValue('modify_member','posixgroupattr'))) {
	$query['filter'] = $_SESSION[APPCONFIG]->getValue('modify_member','posixfilter');
	$attr = $_SESSION[APPCONFIG]->getValue('modify_member','posixattr');

} else {
	$query['filter'] = $_SESSION[APPCONFIG]->getValue('modify_member','filter');
	$attr = $_SESSION[APPCONFIG]->getValue('modify_member','attr');
}

$query['attrs'] = array($attr);

$possible_values = array();
foreach ($app['server']->getBaseDN() as $base) {
	$query['base'] = $base;

	$possible_values = array_merge($possible_values,$app['server']->query($query,null));
}

usort($possible_values,'pla_compare_dns');

$request['page']->drawTitle(sprintf('%s <b>%s</b>',_('Modify group'),get_rdn($request['dn'])));
$request['page']->drawSubTitle();

printf('%s <b>%s</b> %s <b>%s</b>:',
	_('There are'),count($current_members),_('members in group'),htmlspecialchars(get_rdn($request['dn'])));

$possible_members = array();
for ($i=0;$i<count($possible_values);$i++) {
	if (preg_match("/^".$request['attr']."$/i",$_SESSION[APPCONFIG]->getValue('modify_member','posixgroupattr')))
		$possible_members[$i] = $possible_values[$i][$_SESSION[APPCONFIG]->getValue('modify_member','posixattr')][0];
	else
		$possible_members[$i] = $possible_values[$i][$_SESSION[APPCONFIG]->getValue('modify_member','attr')];
}

# Show only user that are not already in group.
$possible_members = array_diff($possible_members,$current_members);
usort($possible_members,'pla_compare_dns');

/* Draw form with select boxes, left for all possible members and
 * right one for those that belong to group */

# Modifications will be sent to update_confirm which takes care of rest of the processing
echo '<br />';
echo '<br />';

echo '<form action="cmd.php" method="post" class="add_value" id="member">';
echo '<div>';
if ($_SESSION[APPCONFIG]->getValue('confirm','update'))
	echo '<input type="hidden" name="cmd" value="update_confirm" />';
else
	echo '<input type="hidden" name="cmd" value="update" />';
echo '</div>';

echo '<table class="modify_members">';

echo '<tr>';
printf('<td><img src="%s/ldap-user.png" alt="Users" /> %s</td>',IMGDIR,_('Available members'));
printf('<td><img src="%s/ldap-uniquegroup.png" alt="Members" /> %s</td>',IMGDIR,_('Group members'));
echo '</tr>';

# Generate select box from all possible members
echo '<tr>';
echo '<td>';
echo '<select name="notmembers" size="10" multiple="multiple">';

switch ($request['attr']) {
	case 'nisnetgrouptriple':
		foreach ($possible_members as $possible) {
			// Added for output formating
			$matches = preg_split('/[=,]/',$possible);
			$possible = sprintf('(,%s,)',$matches[1]);

			if (! in_array($possible,$current_members))
				printf('<option>%s</option>',$possible);
		}

		break;

	case ('memberuid' || 'member' || 'uniquemember'):
		foreach ($possible_members as $possible) {
			if (! in_array($possible,$current_members))
				printf('<option>%s</option>',$possible);
		}

		break;
}

echo '</select>';
echo '</td>';

# Generate select box from all current members
echo '<td>';
echo '<select name="members" size="10" multiple="multiple">';

foreach ($current_members as $current)
	printf('<option>%s</option>',htmlspecialchars($current));

echo '</select>';
echo '</td>';

echo '</tr>';

# Show buttons which move users from left to right and vice versa
echo '<tr>';
echo '<td>';
printf('<input type="button" onclick="one2two()" value="%s >>" />&nbsp;<input type="button" onclick="all2two()" value="%s >>" />',
	_('Add selected'),_('Add all'));
echo '</td>';
echo '<td>';
printf('<input type="button" onclick="two2one()" value="<< %s" />&nbsp;<input type="button" onclick="all2one()" value="<< %s" />',
	_('Remove selected'),('Remove all'));
echo '</td>';
echo '</tr>';

echo '<tr><td colspan="2">';

# Hidden attributes for update_confirm.php
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
printf('<input type="hidden" name="dn" value="%s" />',rawurlencode($request['dn']));
printf('<input type="hidden" name="attr" value="%s" />',$request['attr']);

/* Generate array of input text boxes from current members.
 * update_confirm.php will see this as old_values[member-attribute][item] */
for ($i=0; $i<count($current_members); $i++)
	printf('<input type="hidden" name="old_values[%s][%s]" value="%s" />',
		htmlspecialchars($request['attr']),$i,htmlspecialchars($current_members[$i]));

/* Javascript generates array of input text boxes from new members.
 * update_confirm.php will see this as new_values[member-attribute][item]
 * Input text boxes will be generated to div=dnu */
echo '<div id="dnu">';
printf('<input type="hidden" name="new_values[%s][]" value="" />',htmlspecialchars($request['attr']));
echo '</div>';

# Submit values to update_confirm.php and when clicked, run addSelected
printf('<input type="submit" name="save" value="%s" onclick="update_new_values(\'%s\')" />',_('Save changes'),$request['attr']);
echo '</td></tr>';

echo '</table>';
echo '</form>';
printf('<script type="text/javascript" src="%smodify_member.js"></script>',JSDIR);
?>
