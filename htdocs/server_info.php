<?php
/**
 * Fetches and displays all information that it can from the specified server
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$attrs = $app['server']->getRootDSE();

printf('<h3 class="title">%s%s</h3>',_('Server info for: '),$app['server']->getName());
printf('<h3 class="subtitle">%s</h3>',_('Server reports the following information about itself'));

if (! count($attrs)) {
	echo '<br /><br />';
	printf('<div style="text-align: center;">%s</div>',_('This server has nothing to report.'));
	return;
}

echo '<table class="result" border="0">';
foreach ($attrs as $key => $values) {
	if ($key == 'dn')
		continue;

	echo '<tr class="list_item"><td class="heading" rowspan="2">';

	$sattr = $app['server']->getSchemaAttribute($key);

	if ($sattr && $_SESSION[APPCONFIG]->isCommandAvailable('script','schema') && $_SESSION[APPCONFIG]->getValue('appearance','show_schema_link')) {
		$href = sprintf('cmd.php?cmd=schema&amp;server_id=%s&amp;view=attributes&amp;viewvalue=%s',$app['server']->getIndex(),$sattr->getName());
		printf('<a href="%s" title="%s: %s" >%s</a>',
			$href,_('Click to view the schema definition for attribute type'),$sattr->getName(false),$sattr->getName(false));

	} else
		echo $key;

	echo '</td></tr>';

	echo '<tr class="list_item"><td class="blank">&nbsp;</td><td class="value">';
	echo '<table class="result" border="0">';

	if (is_array($values))
		foreach ($values as $value) {
			$oidtext = '';
			print '<tr>';

			if (preg_match('/^[0-9]+\.[0-9]+/',$value)) {
				printf('<td rowspan="2" style="width: 5%%; vertical-align: top"><img src="%s/rfc.png" title="%s" alt="%s"/></td>',
					IMGDIR,$value,htmlspecialchars($value));

				if ($oidtext = support_oid_to_text($value))
					if (isset($oidtext['ref']))
						printf('<td><acronym title="%s">%s</acronym></td>',$oidtext['ref'],$oidtext['title']);
					else
						printf('<td>%s</td>',$oidtext['title']);

				else
					if (strlen($value) > 0)
						printf('<td><small>%s</small></td>',$value);

			} else {
				printf('<td rowspan="2" colspan="2">%s</td>',$value);
			}

			print '</tr>';

			if (isset($oidtext['desc']) && trim($oidtext['desc']))
				printf('<tr><td><small>%s</small></td></tr>',$oidtext['desc']);
			else
				echo '<tr><td>&nbsp;</td></tr>';

			if ($oidtext)
				echo '<tr><td colspan="2">&nbsp;</td></tr>';
		}

	else
		printf('<tr><td colspan="2">%s&nbsp;</td></tr>',$values);


	echo '</table>';
	echo '</td></tr>';
}
echo '</table>';
?>
