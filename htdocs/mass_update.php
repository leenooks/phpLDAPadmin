<?php
/**
 * Main command page for phpLDAPadmin
 * This script will handle bulk updates.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require_once './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);
$request['mass_values'] = get_request('mass_values','REQUEST',true);

# Check if the entries exist.
$request['update'] = array();

foreach ($request['dn'] as $index => $dn) {
	# Check if the entry exists.
	if (! $dn || ! $app['server']->dnExists($dn)) {
		system_message(array(
			'title'=>_('Entry does not exist'),
			'body'=>sprintf('%s (%s/%s)',_('The entry does not exist and will be ignored'),$dn),
			'type'=>'error'));

		continue;
	}

	# Simulate the requirements for *Render->accept()
	if (! isset($request['mass_values'][$index]))
		continue;

	$_REQUEST['new_values'] = $request['mass_values'][$index];

	$render = new MassRender($app['server']->getIndex(),'none');
	$render->setDN($dn);
	$render->accept(true);

	if ($render->getTemplate()->getLDAPmodify(false,$index))
		$request['update'][$index] = $render;
}

# We can use the $render to give us a title
$render->drawTitle(_('Bulk update the following DNs'));
$render->drawSubTitle(sprintf('%s: <b>%s</b>',_('Server'),$app['server']->getName()));

if (count($request['update'])) {
	if (get_request('confirm','REQUEST')) {
		foreach ($request['update'] as $index => $page) {
			$template = $page->getTemplate();

			# Perform the modification
			$result = $app['server']->modify($template->getDN(),$template->getLDAPmodify(false,$index));

			if ($result)
				printf('%s: <b>%s</b><br>',$template->getDN(),_('Modification successful!'));
			else
				printf('%s: <b>%s</b><br>',$template->getDN(),_('Modification NOT successful!'));
		}

	} else {
		echo '<form action="cmd.php" method="post">';
		echo '<input type="hidden" name="cmd" value="mass_update" />';
		printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
		echo '<input type="hidden" name="confirm" value="1" />';

		foreach ($request['update'] as $j => $page)
			printf('<input type="hidden" name="dn[%s]" value="%s" />',$j,$page->getTemplate()->getDN());

		echo '<table class="result_box" width="100%" border="1">';
		echo '<tr><td>';

		echo '<br/>';

		echo '<table class="result" border="0">';
		echo '<tr><td>';
		printf(_('There will be %s updates done with this mass update'),sprintf('<b>%s</b>',count($request['update'])));
		echo '</td></tr>';
		echo '</table>';

		echo '<br/>';

		foreach ($request['update'] as $index => $page) {
			$template = $page->getTemplate();

			echo '<table class="result" border="0">';
			echo '<tr class="list_title">';
			printf('<td class="icon"><img src="%s/%s" alt="icon" /></td>',IMGDIR,get_icon($app['server']->getIndex(),$template->getDN()));

			printf('<td colspan="3"><a href="cmd.php?cmd=template_engine&amp;server_id=%s&amp;dn=%s">%s</a></td>',
				$app['server']->getIndex(),rawurlencode(dn_unescape($template->getDN())),htmlspecialchars(get_rdn($template->getDN())));
			echo '</tr>';

			printf('<tr class="list_item"><td class="blank">&nbsp;</td><td class="heading">dn</td><td class="value" style="width: 45%%;">%s</td><td class="value" style="width: 45%%;"><b>%s</b></td></tr>',
				htmlspecialchars(dn_unescape($template->getDN())),_('Old Value'));

			foreach ($template->getLDAPmodify(true,$index) as $attribute) {
				echo '<tr class="list_item">';
				echo '<td class="blank">&nbsp;</td>';

				echo '<td class="heading">';
				$page->draw('Name',$attribute);
				echo '</td>';

				# Show NEW Values
				echo '<td><span style="white-space: nowrap;">';

				if (! $attribute->getValueCount() || $attribute->isForceDelete()) {
					printf('<span style="color: red">[%s]</span>',_('attribute deleted'));
					printf('<input type="hidden" name="mass_values[%s][%s][%s]" value="%s" />',$index,$attribute->getName(),0,'');
				}

				foreach ($attribute->getValues() as $key => $value) {
					# For multiple values, we'll highlight the changed ones
					if ((count($attribute->getValues()) > 5) && in_array($value,$attribute->getAddedValues()))
						echo '<span style="color:#004400; background:#FFFFA0">';

					$page->draw('CurrentValue',$attribute,$key);

					# For multiple values, close the highlighting
					if ((count($attribute->getValues()) > 5) && in_array($value,$attribute->getAddedValues()))
						echo '</span>';

					echo '<br />';
					printf('<input type="hidden" name="mass_values[%s][%s][%s]" value="%s" />',$index,$attribute->getName(),$key,$value);
				}

				echo '</span></td>';

				# Show OLD Values
				echo '<td><span style="white-space: nowrap;">';

				if (! $attribute->getOldValues())
					printf('<span style="color: green">[%s]</span>',_('attribute doesnt exist'));

					foreach ($attribute->getOldValues() as $key => $value) {
					# For multiple values, we'll highlight the changed ones
					if ((count($attribute->getOldValues()) > 5) && in_array($value,$attribute->getRemovedValues()) && count($attribute->getValues()))
						echo '<span style="color:#880000; background:#FFFFA0">';

					$page->draw('OldValue',$attribute,$key);

					# For multiple values, close the highlighting
					if ((count($attribute->getOldValues()) > 5) && in_array($value,$attribute->getRemovedValues()) && count($attribute->getValues()))
						echo '</span>';

					echo '<br />';
				}

				echo '</span></td>';

				echo '</tr>';
			}

			echo '</table>';

			echo '<br/>';
		}

		echo '</td></tr>';
		echo '</table>';
		printf('<input type="submit" id="save_button" name="submit" value="%s" />',_('Update Values'));
		echo '</form>';
	}

} else {
	echo '<center>';
	echo _('You made no changes');
	echo '</center>';
}
?>
