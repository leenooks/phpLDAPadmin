<?php
// $Header$

/**
 * Takes the results of clicking "Save" in template_engine.php and determines
 * which attributes need to be updated (ie, which ones actually changed). Then,
 * we present a confirmation table to the user outlining the changes they are
 * about to make. That form submits directly to update.php, which makes the
 * change.
 *
 * @package phpLDAPadmin
 * @subpackage Page
 * @see update.php
 */

/**
 */

require './common.php';

$request = array();
$request['dn'] = get_request('dn','REQUEST',true);

if (! $request['dn'] || ! $app['server']->dnExists($request['dn']))
	error(sprintf(_('The entry (%s) does not exist.'),$request['dn']),'error','index.php');

$request['page'] = new PageRender($app['server']->getIndex(),get_request('template','REQUEST',false,'none'));
$request['page']->setDN($request['dn']);
$request['page']->accept();
$request['template'] = $request['page']->getTemplate();

$request['page']->drawTitle(get_rdn($request['template']->getDN()));
$request['page']->drawSubTitle();

# Confirm the updates
if (count($request['template']->getLDAPmodify(true))) {
	echo '<center>';
	echo _('Do you want to make these changes?');
	echo '<br /><br />';

	echo "\n\n";
	echo '<form action="cmd.php" method="post">';
	echo '<input type="hidden" name="cmd" value="update" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',htmlspecialchars($request['dn']));
	echo "\n";

	$request['page']->drawHiddenAttributes();

	echo '<table class="result_table">';
	echo "\n";

	printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
		_('Attribute'),_('Old Value'),_('New Value'),_('Skip'));
	echo "\n\n";

	$counter = 0;
	foreach ($request['template']->getLDAPmodify(true) as $attribute) {
		$counter++;

		printf('<tr class="%s">',$counter%2 ? 'even' : 'odd');
		printf('<td><b>%s</b></td>',$attribute->getFriendlyName());

		# Show OLD Values
		echo '<td><span style="white-space: nowrap;">';

		if (! $attribute->getOldValues())
			printf('<span style="color: green">[%s]</span>',_('attribute doesnt exist'));

		foreach ($attribute->getOldValues() as $key => $value) {
			# For multiple values, we'll highlight the changed ones
			if ((count($attribute->getOldValues()) > 5) && in_array($value,$attribute->getRemovedValues()) && count($attribute->getValues()))
				echo '<span style="color:#880000; background:#FFFFA0">';

			$request['page']->draw('OldValue',$attribute,$key);

			# For multiple values, close the highlighting
			if ((count($attribute->getOldValues()) > 5) && in_array($value,$attribute->getRemovedValues()) && count($attribute->getValues()))
				echo '</span>';

			echo '<br />';
		}

		echo '</span></td>';

		# Show NEW Values
		echo '<td><span style="white-space: nowrap;">';

		if (! $attribute->getValueCount() || $attribute->isForceDelete())
			printf('<span style="color: red">[%s]</span>',_('attribute deleted'));

		foreach ($attribute->getValues() as $key => $value) {
			# For multiple values, we'll highlight the changed ones
			if ((count($attribute->getValues()) > 5) && in_array($value,$attribute->getAddedValues()))
				echo '<span style="color:#004400; background:#FFFFA0">';

			$request['page']->draw('CurrentValue',$attribute,$key);

			# For multiple values, close the highlighting
			if ((count($attribute->getValues()) > 5) && in_array($value,$attribute->getAddedValues()))
				echo '</span>';

			echo '<br />';
		}

		echo '</span></td>';

		# Show SKIP Option
		$input_disabled = '';
		$input_onclick = '';

		if ($attribute->isForceDelete())
			$input_disabled = 'disabled="disabled"';

		if ($attribute->getName() == 'objectclass' && (count($request['template']->getForceDeleteAttrs()) > 0)) {
			$input_onclick = 'onclick="if (this.checked) {';

			foreach ($request['template']->getForceDeleteAttrs() as $ad_name) {
				$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = false;",$ad_name->getName());
				$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = true;",$ad_name->getName());
			}

			$input_onclick .= '} else {';
			foreach ($request['template']->getForceDeleteAttrs() as $ad_name) {
				$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = false;",$ad_name->getName());
				$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = true;",$ad_name->getName());
			}
			$input_onclick .= '}"';
		}

		printf('<td><input name="skip_array[%s]" id="skip_array_%s" type="checkbox" %s %s/></td>',
			htmlspecialchars($attribute->getName()),htmlspecialchars($attribute->getName()),$input_disabled,$input_onclick);
		echo '</tr>';
		echo "\n\n";
	}

	echo '</table>';

	echo '<br />';
	printf('<input type="submit" value="%s" />',_('Commit'));
	printf('<input type="submit" name="cancel" value="%s" />',_('Cancel'));
	echo '</form>';
	echo '<br />';

	if (count($request['template']->getForceDeleteAttrs()) > 0) {
		echo '<table class="result_table"><tr>';
		printf('<td class="heading">%s:</td>',_('The deletion of objectClass(es)'));
		printf('<td class="value"><b>%s</b></td>',implode('</b>, <b>',$request['template']->getAttribute('objectclass')->getRemovedValues()));
		echo '</tr><tr>';
		printf('<td class="heading">%s:</td>',_('will delete the attribute(s)'));
		echo '<td class="value"><b>';

		$i = 0;
		foreach ($request['template']->getForceDeleteAttrs() as $attribute) {
			if ($i++ != 0)
				echo '</b>, <b>';

			echo $_SESSION[APPCONFIG]->getFriendlyHTML($attribute);
		}
		echo '</b></td></tr></table>';
	}

	echo '</center>';

} else {
	echo '<center>';
	echo _('You made no changes');
	$href = sprintf('cmd.php?cmd=template_engine&server_id=%s&dn=%s',
		 $app['server']->getIndex(),rawurlencode($request['dn']));

	printf(' <a href="%s">%s</a>.',htmlspecialchars($href),_('Go back'));
	echo '</center>';
}
?>
