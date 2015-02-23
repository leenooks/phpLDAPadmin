<?php
/**
 * Displays the information from the monitor context
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

$attrs = $app['server']->getRootDSE();

$query = array();
$query['base'] = $attrs['monitorcontext'][0];
$query['scope'] = 'sub';
$query['attrs'] = array('+','*');
$results = $app['server']->query($query,null);

if (! isset($attrs['monitorcontext']) || ! count($results))
	system_message(array(
		'title'=>_('Monitoring context does not exist'),
		'body'=>sprintf('%s: <b>%s</b>',_('Could not obtain the monitor context for this server'),$app['server']->getName()),
		'type'=>'warn'),'index.php');

printf('<h3 class="title">%s%s</h3>',_('Monitor info for: '),$app['server']->getName());
printf('<h3 class="subtitle">%s</h3>',_('Server reports the following information about itself'));

echo '<table class="result" border="0">';

# cn=Monitor
printf('<tr class="list_item"><td class="heading" rowspan="2">%s</td></tr>',_('LDAP Server'));
printf('<tr class="list_item"><td class="value">');

echo '<table class="result" border="0">';
printf('<tr><td>%s</td></tr>',$results[$attrs['monitorcontext'][0]]['monitoredinfo'][0]);
echo '</table>';

echo '</td></tr>';

foreach (array(
	'cn=Backends,cn=Monitor' => 'cn=Backend %s,%s',
	'cn=Overlays,cn=Monitor' => 'cn=Overlay %s,%s'
	) as $dn => $child) {

	if (isset($results[$dn]['description'])) {
		$description = implode(' ',$results[$dn]['description']);

		$description = preg_replace('/"/','\'',$description);
	} else {
		$description = '';
	}

	printf('<tr class="list_item"><td class="heading" rowspan="2"><acronym title="%s">%s</acronym></td></tr>',$description,$dn);
	echo '<tr class="list_item"><td class="value">';
	echo '<table class="result"><tr><td>';
	echo '<table class="result_table" border="0" width="100%">';

	$attrs = array(
		'monitorruntimeconfig',
		'supportedcontrol'
		);

	echo '<tr class="highlight">';
	printf('<td style="width: 10%%;">%s</td><td style="width: 20%%;">%s</td>',_('Type'),'namingContext');

	foreach ($attrs as $attr)
		printf('<td style="width: 20%%;">%s</td>',$attr);

	echo '</tr>';

	$counter = 0;
	foreach ($results[$dn]['monitoredinfo'] as $index => $backend) {
		printf('<tr class="%s">',$counter++%2==0?'even':'odd');
		printf('<td>%s</td>',$backend);

		$key = sprintf($child,$index,$dn);

		echo '<td>';
		if (isset($results[$key]['seealso'])) {
			$seealso = is_array($results[$key]['seealso']) ? $results[$key]['seealso'] : array($results[$key]['seealso']);

			foreach ($seealso as $db)
				if (isset($results[$db]['namingcontexts']))
					printf('<acronym title="%s">%s</acronym><br/>',
						isset($results[$db]['labeleduri']) ? implode(' ',$results[$db]['labeleduri']) : _('Internal'),
						implode(' ',$results[$db]['namingcontexts']));
				else
					printf('%s ',implode(' ',$results[$db]['monitoredinfo']));

		} else {
			echo '&nbsp;';
		}
		echo '</td>';

		foreach ($attrs as $attr) {
			echo '<td>';
			if (isset($results[$key][$attr])) {
				if (! is_array($results[$key][$attr]))
					$sc = array($results[$key][$attr]);
				else
					$sc = $results[$key][$attr];

				if (strcasecmp('supportedcontrol',$attr) == 0)
					foreach ($sc as $control) {
						$oidtotext = support_oid_to_text($control);

						printf('<acronym title="%s">%s</acronym><br/>',
							$control,$oidtotext['title']);
					}

				else
					printf('%s ',implode('<br/>',$sc));

			} else {
				echo '&nbsp;';
			}

			echo '</td>';
		}
		echo '</tr>';
	}

	echo '</table></td></tr>';
	echo '</table>';
	echo '</td></tr>';
}

# cn=Connections,cn=Monitor
printf('<tr class="list_item"><td class="heading" rowspan="2"><acronym title="%s">%s</acronym></td></tr>',$results['cn=Connections,cn=Monitor']['description'][0],_('LDAP Connections'));
printf('<tr class="list_item"><td class="value">');
echo '<table class="result"><tr><td>';
echo '<table class="result_table" border="0" width="100%">';

printf('<tr class="highlight"><td class="20%%">%s</td><td class="value" style="width: 80%%;">%s</td></tr>',
	_('Total Connections'),$results['cn=Total,cn=Connections,cn=Monitor']['monitorcounter'][0]);
printf('<tr class="highlight"><td class="20%%">%s</td><td class="value" style="width: 80%%;">%s</td></tr>',
	_('Current Connections'),$results['cn=Current,cn=Connections,cn=Monitor']['monitorcounter'][0]);

# Look for some connections
foreach ($results as $key => $value) {
	if (preg_match('/^cn=Connection.*,cn=Connections,cn=Monitor$/',$key)) {
		echo '<tr class="highlight">';
		printf('<td>%s</td>',$results[$key]['cn'][0]);

		echo '<td class="value">';
		echo '<table class="result_table" border="0" width="100%">';

		$counter = 0;
		foreach (array(
			'monitorconnectionactivitytime',
			'monitorconnectionauthzdn',
			'monitorconnectionget',
			'monitorconnectionlistener',
			'monitorconnectionlocaladdress',
			'monitorconnectionmask',
			'monitorconnectionnumber',
			'monitorconnectionopscompleted',
			'monitorconnectionopsexecuting',
			'monitorconnectionopspending',
			'monitorconnectionopsreceived',
			'monitorconnectionpeeraddress',
			'monitorconnectionpeerdomain',
			'monitorconnectionprotocol',
			'monitorconnectionread',
			'monitorconnectionstarttime',
			'monitorconnectionwrite'
			) as $metric) {

			printf('<tr class="%s">',$counter++%2==0?'even':'odd');

			printf('<td class="title" style="width: 35%%;">%s</td><td style="width: 65%%;">%s</td>',
				$metric,isset($results[$key][$metric]) ? $results[$key][$metric][0] : '&nbsp;');
			echo '</tr>';
		}

		echo '</table>';
		echo '</td>';
		echo '</tr>';
	}
}

echo '</table></td></tr>';
echo '</table>';
echo '</td></tr>';

foreach (array(
	'cn=Listeners,cn=Monitor',
	'cn=Log,cn=Monitor',
	'cn=Operations,cn=Monitor',
	'cn=SASL,cn=Monitor',
	'cn=TLS,cn=Monitor',
	'cn=Statistics,cn=Monitor',
	'cn=Threads,cn=Monitor',
	'cn=Time,cn=Monitor',
	'cn=Waiters,cn=Monitor'
	) as $dn ) {

	$description = implode(' ',$results[$dn]['description']);
	$description = preg_replace('/"/','\'',$description);

	printf('<tr class="list_item"><td class="heading" rowspan="2"><acronym title="%s">%s</acronym></td></tr>',$description,$dn);
	echo '<tr class="list_item"><td class="value">';
	echo '<table class="result"><tr><td>';
	echo '<table class="result_table" border="0" width="100%">';

	if (isset($results[$dn]['monitoropinitiated']))
		printf('<tr class="highlight"><td style="width: 20%%;">%s</td><td class="value" style="width: 80%%;">%s</td></tr>',
			'monitorOpInitiated',$results[$dn]['monitoropinitiated'][0]);
	if (isset($results[$dn]['monitoropcompleted']))
		printf('<tr class="highlight"><td style="width: 20%%;">%s</td><td class="value" style="width: 80%%;">%s</td></tr>',
			'monitorOpCompleted',$results[$dn]['monitoropcompleted'][0]);
	if (isset($results[$dn]['monitoredinfo']))
		printf('<tr class="highlight"><td style="width: 20%%;">%s</td><td class="value" style="width: 80%%;">%s</td></tr>',
			'monitoredInfo',$results[$dn]['monitoredinfo'][0]);

	# Look for some connecitons
	foreach ($results as $key => $value) {
		if (preg_match('/^.*,'.$dn.'$/',$key)) {
			echo '<tr class="highlight">';
			printf('<td style="width: 20%%;">%s</td>',$results[$key]['cn'][0]);

			echo '<td class="value" style="width: 80%;">';
			echo '<table class="result_table" border="0" width="100%">';

			foreach (array(
				'labeleduri',
				'monitorconnectionlocaladdress',
				'monitoredinfo',
				'monitorcounter',
				'monitoropinitiated',
				'monitoropcompleted',
				'monitortimestamp'
				) as $metric) {

				if (isset($results[$key][$metric])) {
					printf('<tr class="%s">',$counter++%2==0?'even':'odd');

					printf('<td class="title" style="width: 35%%;">%s</td><td style="width: 65%%;">%s</td>',
						$metric,$results[$key][$metric][0]);

					echo '</tr>';
				}
			}

			echo '</table>';
			echo '</td>';
			echo '</tr>';
		}
	}
	echo '</table></td></tr>';
	echo '</table>';
	echo '</td></tr>';
}

echo '</table>';
?>
