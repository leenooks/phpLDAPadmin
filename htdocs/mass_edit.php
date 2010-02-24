<?php
/**
 * Main command page for phpLDAPadmin
 * Enable mass editing of Attribute values from a list of DNs.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require_once './common.php';

# The DN we are working with
$request = array();
$request['dn'] = get_request('dn','REQUEST');
$request['attrs'] = get_request('attrs','REQUEST');

# Check if the entries exist.
$counter = 0;
$attrcols = array();
foreach ($request['dn'] as $dn) {
	# Check if the entry exists.
	if (! $dn || ! $app['server']->dnExists($dn)) {
		system_message(array(
			'title'=>_('Entry does not exist'),
			'body'=>sprintf('%s (%s/%s)',_('The entry does not exist and will be ignored'),$dn),
			'type'=>'error'));

		continue;
	}

	$request['page'][$counter] = new MassRender($app['server']->getIndex(),'none');
	$request['page'][$counter]->setDN($dn);
	$request['page'][$counter]->accept(true);

	$template = $request['page'][$counter]->getTemplate();

	# Mark our attributes to edit as shown.
	foreach ($template->getAttributes(true) as $attribute) {
		if ($attribute->isInternal())
			continue;

		if (in_array_ignore_case($attribute->getName(),$request['attrs']) || in_array('*',$request['attrs'])) {
			$attribute->show();

			# Get a list of our columns (we are not interested in these attribute values)
			if (! isset($attrcols[$attribute->getName()]))
				$attrcols[$attribute->getName()] = $attribute;
		}
	}

	$counter++;
}

usort($attrcols,'sortAttrs');

if (! count($request['page']))
	header('Location: index.php');

# We'll render this forms Title with the first DN's object.
$request['page'][0]->drawTitle(_('Bulk edit the following DNs'));
$request['page'][0]->drawSubTitle(sprintf('%s: <b>%s</b>',_('Server'),$app['server']->getName()));

echo '<form action="cmd.php" method="post">';
echo '<div>';
echo '<input type="hidden" name="cmd" value="mass_update" />';
printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());

foreach ($request['page'] as $j => $page)
	printf('<input type="hidden" name="dn[%s]" value="%s" />',$j,$page->getTemplate()->getDN());

echo '</div>';

echo '<table class="result_table" border="0">';
echo '<tr class="heading">';
echo '<td>DN</td>';

foreach ($attrcols as $attribute) {
	echo '<td>';
	$request['page'][0]->draw('Name',$attribute);
	echo '</td>';
}

echo '</tr>';

$counter = 0;
foreach ($request['page'] as $j => $page) {
	$template = $page->getTemplate();

	printf('<tr class="%s">',$counter++%2==0?'even':'odd');
	printf('<td><span style="white-space: nowrap;"><acronym title="%s"><b>%s</b>...</acronym></span></td>',
		$template->getDN(),substr($template->getDN(),0,20));

	foreach ($attrcols as $attrcol) {
		$attribute = $template->getAttribute($attrcol->getName());

		echo '<td>';
		if ($attribute) {
			foreach ($attribute->getValues() as $i => $val)
				$page->draw('MassFormReadWriteValue',$attribute,$i,$j);

		# The attribute doesnt exist. If it is available by the shema, we can draw an empty input box.
		} else {
			$match = false;

			foreach ($template->getAvailAttrs() as $attribute) {
				if ($attrcol->getName() == $attribute->getName()) {
					$page->draw('MassFormReadWriteValue',$attribute,0,$j);
					$match = true;

					break;
				}
			}

			if (! $match)
				printf('<center><small>%s</small></center>', _('Attribute not available'));
		}

		echo '</td>';
	}

	echo '</tr>';
}

echo '</table>';
echo '<div>';
echo '<br/>';
printf('<input type="submit" id="save_button" name="submit" value="%s" />',_('Update Values'));
echo '</div>';
echo '</form>';
?>
