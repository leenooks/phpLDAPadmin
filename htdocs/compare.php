<?php
/**
 * Compares two DN entries side by side.
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

if (! $ldap['SRC']->dnExists($request['dnSRC']))
	error(sprintf('%s (%s)',_('No such entry.'),pretty_print_dn($request['dnSRC'])),'error','index.php');

if (! $ldap['DST']->dnExists($request['dnDST']))
	error(sprintf('%s (%s)',_('No such entry.'),pretty_print_dn($request['dnDST'])),'error','index.php');

$request['pageSRC'] = new PageRender($ldap['SRC']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['pageSRC']->setDN($request['dnSRC']);
$request['pageSRC']->accept();
$request['templateSRC'] = $request['pageSRC']->getTemplate();

$request['pageDST'] = new PageRender($ldap['DST']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['pageDST']->setDN($request['dnDST']);
$request['pageDST']->accept();
$request['templateDST'] = $request['pageDST']->getTemplate();

# Get a list of all attributes.
$attrs_all = array_unique(array_merge($request['templateSRC']->getAttributeNames(),$request['templateDST']->getAttributeNames()));

$request['pageSRC']->drawTitle(_('Comparing the following DNs'));

echo '<br/>';

echo '<table class="entry" width="100%" border="0">';
echo '<tr class="heading">';

$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
	$ldap['SRC']->getIndex(),rawurlencode($request['dnSRC']));
printf('<td colspan="2" style="width: 40%%;">%s: <b>%s</b><br />%s: <b><a href="%s">%s</a></b></td>',
	_('Server'),$ldap['SRC']->getName(),_('Distinguished Name'),
	htmlspecialchars($href),$request['dnSRC']);

$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
	$ldap['DST']->getIndex(),rawurlencode($request['dnDST']));
printf('<td colspan="2" style="width: 40%%;">%s: <b>%s</b><br />%s: <b><a href="%s">%s</a></b></td>',
	_('Server'),$ldap['DST']->getName(),_('Distinguished Name'),
	htmlspecialchars($href),$request['dnDST']);

echo '</tr>';

echo '<tr>';
echo '<td colspan="4" style="text-align: right;">';
echo '<form action="cmd.php?cmd=compare" method="post">';
echo '<div>';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
printf('<input type="hidden" name="server_id_src" value="%s" />',$ldap['DST']->getIndex());
printf('<input type="hidden" name="server_id_dst" value="%s" />',$ldap['SRC']->getIndex());
printf('<input type="hidden" name="dn_src" value="%s" />',htmlspecialchars($request['dnDST']));
printf('<input type="hidden" name="dn_dst" value="%s" />',htmlspecialchars($request['dnSRC']));
printf('<input type="submit" value="%s" />',_('Switch Entry'));
echo '</div>';
echo '</form>';
echo '</td>';
echo '</tr>';

if (! is_array($attrs_all) || ! count($attrs_all)) {
	printf('<tr><td colspan="4">(%s)</td></tr>',_('This entry has no attributes'));
	print '</table>';

	return;
}

sort($attrs_all);

# Work through each of the attributes.
foreach ($attrs_all as $attr) {
	# Has the config.php specified that this attribute is to be hidden or shown?
	if ($ldap['SRC']->isAttrHidden($attr) || $ldap['DST']->isAttrHidden($attr))
		continue;

	$attributeSRC = $request['templateSRC']->getAttribute($attr);
	$attributeDST = $request['templateDST']->getAttribute($attr);

	# Get the values and see if they are the same.
	if ($attributeSRC && $attributeDST && ($attributeSRC->getValues() == $attributeDST->getValues()))
		echo '<tr>';
	else
		echo '<tr>';

	foreach (array('src','dst') as $side) {
		# If we are on the source side, show the attribute name.
		switch ($side) {
			case 'src':
				if ($attributeSRC) {
					echo '<td class="title">';
					$request['pageSRC']->draw('Name',$attributeSRC);
					echo '</td>';

					if ($request['pageSRC']->getServerID() == $request['pageDST']->getServerID())
						echo '<td class="title">&nbsp;</td>';

					else {
						echo '<td class="note" style="text-align: right;">';
						$request['pageSRC']->draw('Notes',$attributeSRC);
						echo '</td>';
					}

				} else {
					echo '<td colspan="2">&nbsp;</td>';
				}

				break;

			case 'dst':
				if ($attributeDST) {
					if ($attributeSRC && ($request['pageSRC']->getServerID() == $request['pageDST']->getServerID()))
						echo '<td class="title">&nbsp;</td>';

					else {
						echo '<td class="title" >';
						$request['pageDST']->draw('Name',$attributeDST);
						echo '</td>';
					}

					echo '<td class="note" style="text-align: right;">';
					$request['pageDST']->draw('Notes',$attributeDST);
					echo '</td>';

				} else {
					echo '<td colspan="2">&nbsp;</td>';
				}

				break;
		}
	}

	echo '</tr>';
	echo "\n\n";

	# Get the values and see if they are the same.
	if ($attributeSRC && $attributeDST && ($attributeSRC->getValues() == $attributeDST->getValues()))
		echo '<tr style="background-color: #F0F0F0;">';
	else
		echo '<tr>';

	foreach (array('src','dst') as $side) {
		echo '<td class="value" colspan="2"><table border="0">';
		echo '<tr><td>';

		switch ($side) {
			case 'src':

				if ($attributeSRC && count($attributeSRC->getValues()))
					$request['pageSRC']->draw('CurrentValues',$attributeSRC);
				else
					echo '&nbsp;';

				break;

			case 'dst':
				if ($attributeDST && count($attributeDST->getValues()))
					$request['pageDST']->draw('CurrentValues',$attributeDST);
				else
					echo '&nbsp;';

				break;
		}

		echo '</td></tr>';
		echo '</table></td>';
	}

	echo '</tr>';
}
echo '</table>';
?>
