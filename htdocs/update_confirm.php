<?php
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
	echo '<div style="text-align: center;">';
	echo _('Do you want to make these changes?');
	echo '<br /><br />';
	echo '</div>';

	echo "\n\n";
	echo '<form action="cmd.php" method="post" id="update_form">';
	echo '<div>';
	echo '<input type="hidden" name="cmd" value="update" />';
	printf('<input type="hidden" name="server_id" value="%s" />',$app['server']->getIndex());
	printf('<input type="hidden" name="dn" value="%s" />',$request['template']->getDNEncode(false));
	echo "\n";

	$request['page']->drawHiddenAttributes();
	echo '</div>';

	echo '<table class="result_table" style="margin-left: auto; margin-right: auto;">';
	echo "\n";

	printf('<tr class="heading"><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
		_('Attribute'),_('Old Value'),_('New Value'),_('Skip'));
	echo "\n\n";

	# If we skip objectclass changes, but there are new must/may attrs provided by the new objectclass, they need to be skip.
	$mustattrs = getMustAttrs($request['template']->getAttribute('objectclass')->getValues());

	$counter = 0;
	foreach ($request['template']->getLDAPmodify(true) as $attribute) {
		$counter++;

		printf('<tr class="%s">',$counter%2 ? 'even' : 'odd');
		printf('<td><b>%s</b></td>',$attribute->getFriendlyName());

		# Show OLD Values
		echo '<td><span style="white-space: nowrap;">';

		if (! $attribute->getOldValues())
			printf('<span style="color: green">[%s]</span>',_('attribute doesnt exist'));

		$dv = $attribute->getRemovedValues();
		foreach ($attribute->getOldValues() as $key => $value) {
			# For multiple values, we'll highlight the changed ones
			if ($x = ((count($attribute->getOldValues()) > 5) && count($attribute->getValues()) && in_array($value,$dv)))
				echo '<span style="color:#880000; background:#FFFFA0">';

			$request['page']->draw('OldValue',$attribute,$key);

			# For multiple values, close the highlighting
			if ($x)
				echo '</span>';

			echo '<br />';
		}

		echo '</span></td>';

		# Show NEW Values
		echo '<td><span style="white-space: nowrap;">';

		if (! $attribute->getValueCount() || $attribute->isForceDelete())
			printf('<span style="color: red">[%s]</span>',_('attribute deleted'));

		$dv = $attribute->getAddedValues();
		foreach ($attribute->getValues() as $key => $value) {
			# For multiple values, we'll highlight the changed ones
			if ($x = ((count($attribute->getValues()) > 5) && count($attribute->getOldValues()) && in_array($value,$dv)))
				echo '<span style="color:#004400; background:#FFFFA0">';

			$request['page']->draw('CurrentValue',$attribute,$key);

			# For multiple values, close the highlighting
			if ($x)
				echo '</span>';

			echo '<br />';
		}

		echo '</span></td>';

		# Show SKIP Option
		$input_disabled = '';
		$input_onclick = '';

		if ($attribute->isForceDelete() || (in_array($attribute->getName(),$mustattrs)) && $request['template']->getAttribute('objectclass')->justModified())
			$input_disabled = 'disabled="disabled"';

		if ($attribute->getName() == 'objectclass') {
			$input_onclick = '';

			# If there are attributes being force deleted...
			if (count($request['template']->getForceDeleteAttrs()) > 0) {
				$input_onclick = 'onclick="if (this.checked) {';

				# And this OC is being skipped, then these attributes can be optionally deleted.
				foreach ($request['template']->getForceDeleteAttrs() as $ad_name) {
					# Only if it is not a must attr by this objectclass now staying
					if (! in_array($ad_name->getName(),getMustAttrs($attribute->getOldValues())))
						$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = false;",$ad_name->getName());

					$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = true;",$ad_name->getName());
					$input_onclick .= "\n";
				}

				$input_onclick .= '} else {';

				# Otherwise the attributes must be deleted.
				foreach ($request['template']->getForceDeleteAttrs() as $ad_name) {
					$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = false;",$ad_name->getName());
					$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = true;",$ad_name->getName());
					$input_onclick .= "\n";
				}

				$input_onclick .= '};';
			}

			# If the attributes arent force deleted...
			if ($input_onclick)
				$input_onclick .= 'if (this.checked) {';
			else
				$input_onclick = 'onclick="if (this.checked) {';

			# IE: There are new objectclasses that result in new values.
			foreach ($request['template']->getLDAPmodify(true) as $skipattr) {
				if (! $skipattr->getOldValues()) {
					if (! in_array($skipattr->getName(),$mustattrs))
						$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = true;",$skipattr->getName());

					$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = true;",$skipattr->getName());
					$input_onclick .= "\n";
				}
			}

			$input_onclick .= '} else {';

			foreach ($request['template']->getLDAPmodify(true) as $skipattr) {
				if (! $skipattr->getOldValues()) {
					if (! in_array($skipattr->getName(),$mustattrs))
						$input_onclick .= sprintf("document.getElementById('skip_array_%s').disabled = false;",$skipattr->getName());

					$input_onclick .= sprintf("document.getElementById('skip_array_%s').checked = false;",$skipattr->getName());
					$input_onclick .= "\n";
				}
			}

			$input_onclick .= '};"';
		}

		printf('<td><input name="skip_array[%s]" id="skip_array_%s" type="checkbox" %s %s/></td>',
			htmlspecialchars($attribute->getName()),htmlspecialchars($attribute->getName()),$input_disabled,$input_onclick);
		echo '</tr>';
		echo "\n\n";
	}

	echo '</table>';

	echo '<div style="text-align: center;">';
	echo '<br />';
	// @todo cant use AJAX here, it affects file uploads.
	printf('<input type="submit" value="%s" />',
		_('Update Object'));

	printf('<input type="submit" name="cancel" value="%s" %s/>',
		_('Cancel'),
		(isAjaxEnabled() ? sprintf('onclick="return ajDISPLAY(\'BODY\',\'cmd=template_engine&dn=%s\',\'%s\');"',htmlspecialchars($request['dn']),_('Retrieving DN')) : ''));

	echo '</div>';
	echo '</form>';
	echo '<br />';

	if (count($request['template']->getForceDeleteAttrs()) > 0) {
		echo '<table class="result_table" style="margin-left: auto; margin-right: auto;"><tr>';
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

} else {
	$href = sprintf('cmd=template_engine&server_id=%s&dn=%s',
		 $app['server']->getIndex(),$request['template']->getDNEncode());

	echo '<div style="text-align: center;">';
	echo _('You made no changes');

	if (isAjaxEnabled())
		printf(' <a href="cmd.php?%s" onclick="return ajDISPLAY(\'BODY\',\'%s\',\'%s\');">%s</a>.',
			htmlspecialchars($href),htmlspecialchars($href),_('Retrieving DN'),_('Go back'));
	else
		printf(' <a href="cmd.php?%s">%s</a>.',htmlspecialchars($href),_('Go back'));

	echo '</div>';
}

function getMustAttrs($oclasses) {
	global $app;

	$mustattrs = array();

	foreach ($oclasses as $value) {
		$soc = $app['server']->getSchemaObjectClass($value);

		if ($soc)
			foreach ($soc->getMustAttrs() as $sma)
				array_push($mustattrs,$sma->getName());
	}

	return $mustattrs;
}
?>
