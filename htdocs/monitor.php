<?php
// $Header$

/**
 * Displays the information from the monitor context
 *
 * @package phpLDAPadmin
 * @subpackage Page
 */

/**
 */

require './common.php';

# Fetch basic RootDSE attributes using the + and *.
$query = array();
$query['base'] = '';
$query['scope'] = 'base';
$query['attrs'] = $app['server']->getValue('server','root_dse_attributes');
$query['baseok'] = true;
$results = $app['server']->query($query,null);

$attrs = array_change_key_case(array_pop($results));

$query['base'] = $attrs['monitorcontext'];
$query['scope'] = 'sub';
$results = $app['server']->query($query,null);

if (! isset($attrs['monitorcontext']) || ! count($results))
	system_message(array(
		'title'=>_('Monitoring context does not exist'),
		'body'=>sprintf('%s: <b>%s</b>',_('Could not optain the monitor context for this server'),$app['server']->getName()),
		'type'=>'warn'),'index.php');

printf('<h3 class="title">%s%s</h3>',_('Monitor info for: '),$app['server']->getName());
printf('<h3 class="subtitle">%s</h3>',_('Server reports the following information about itself'));

echo '<table class="result" border=0>';

# cn=Monitor
printf('<tr class="list_item"><td class="heading" rowspan=2>%s</td></tr>',_('LDAP Server'));
printf('<tr class="list_item"><td class="value">');

echo '<table class="result" border=0>';
printf('<tr><td>%s</td></tr>',$results['cn=Monitor']['monitoredInfo']);
echo '</table>';

echo '</td></tr>';

foreach (array(
	'cn=Backends,cn=Monitor' => 'cn=Backend %s,%s',
	'cn=Overlays,cn=Monitor' => 'cn=Overlay %s,%s'
	) as $dn => $child) {

	if (is_array($results[$dn]['description']))
		$description = implode(' ',$results[$dn]['description']);
	else
		$description = $results[$dn]['description'];

	$description = preg_replace('/"/','\'',$description);
	printf('<tr class="list_item"><td class="heading" rowspan=2><acronym title="%s">%s</acronym></td></tr>',$description,$dn);
	echo '<tr class="list_item"><td class="value">';
	echo '<table class="result"><tr><td>';
	echo '<table class="result_table" border=0>';

	$attrs = array(
		'monitorRuntimeConfig',
		'supportedControl'
		);

	echo '<tr class="highlight">';
	printf('<td>%s</td><td>%s</td>',_('Type'),'namingContext');

	foreach ($attrs as $attr)
		printf('<td>%s</td>',$attr);

	echo '</tr>';

	$counter = 0;
	foreach ($results[$dn]['monitoredInfo'] as $index => $backend) {
		printf('<tr class="%s">',$counter++%2==0?'even':'odd');
		printf('<td>%s</td>',$backend);

		$key = sprintf($child,$index,$dn);

		echo '<td>';
		if (isset($results[$key]['seeAlso'])) {
			$seealso = is_array($results[$key]['seeAlso']) ? $results[$key]['seeAlso'] : array($results[$key]['seeAlso']);

			foreach ($seealso as $db)
				if (isset($results[$db]['namingContexts']))
					printf('<acronym title="%s">%s</acronym><br/>',isset($results[$db]['labeledURI']) ? $results[$db]['labeledURI'] : _('Internal'),$results[$db]['namingContexts']);
				else
					printf('%s ',$results[$db]['monitoredInfo']);

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

				if (strcasecmp('supportedControl',$attr) == 0)
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
printf('<tr class="list_item"><td class="heading" rowspan=2><acronym title="%s">%s</acronym></td></tr>',$results['cn=Connections,cn=Monitor']['description'],_('LDAP Connections'));
printf('<tr class="list_item"><td class="value">');
echo '<table class="result"><tr><td>';
echo '<table class="result_table" border=0>';

printf('<tr class="highlight"><td>%s</td><td class="value">%s</td></tr>',
	_('Total Connections'),$results['cn=Total,cn=Connections,cn=Monitor']['monitorCounter']);
printf('<tr class="highlight"><td>%s</td><td class="value">%s</td></tr>',
	_('Current Connections'),$results['cn=Current,cn=Connections,cn=Monitor']['monitorCounter']);

# Look for some connections
foreach ($results as $key => $value) {
	if (preg_match('/^cn=Connection.*,cn=Connections,cn=Monitor$/',$key)) {
		echo '<tr class="highlight">';
		printf('<td>%s</td>',$results[$key]['cn']);

		echo '<td class="value">';
		echo '<table class="result_table" border=0>';

		$counter = 0;
		foreach (array(
			'monitorConnectionActivityTime',
			'monitorConnectionAuthzDN',
			'monitorConnectionGet',
			'monitorConnectionListener',
			'monitorConnectionLocalAddress',
			'monitorConnectionMask',
			'monitorConnectionNumber',
			'monitorConnectionOpsCompleted',
			'monitorConnectionOpsExecuting',
			'monitorConnectionOpsPending',
			'monitorConnectionOpsReceived',
			'monitorConnectionPeerAddress',
			'monitorConnectionPeerDomain',
			'monitorConnectionProtocol',
			'monitorConnectionRead',
			'monitorConnectionStartTime',
			'monitorConnectionWrite'
			) as $metric) {

			printf('<tr class="%s">',$counter++%2==0?'even':'odd');

			printf('<td class="title">%s</td><td>%s</td>',
				$metric,isset($results[$key][$metric]) ? $results[$key][$metric] : '&nbsp;');
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

	if (is_array($results[$dn]['description']))
		$description = implode(' ',$results[$dn]['description']);
	else
		$description = $results[$dn]['description'];

	$description = preg_replace('/"/','\'',$description);
	printf('<tr class="list_item"><td class="heading" rowspan=2><acronym title="%s">%s</acronym></td></tr>',$description,$dn);
	echo '<tr class="list_item"><td class="value">';
	echo '<table class="result"><tr><td>';
	echo '<table class="result_table" border=0>';

	if (isset($results[$dn]['monitorOpInitiated']))
		printf('<tr class="highlight"><td>%s</td><td class="value">%s</td></tr>',
			'monitorOpInitiated',$results[$dn]['monitorOpInitiated']);
	if (isset($results[$dn]['monitorOpCompleted']))
		printf('<tr class="highlight"><td>%s</td><td class="value">%s</td></tr>',
			'monitorOpCompleted',$results[$dn]['monitorOpCompleted']);
	if (isset($results[$dn]['monitoredInfo']))
		printf('<tr class="highlight"><td>%s</td><td class="value">%s</td></tr>',
			'monitoredInfo',$results[$dn]['monitoredInfo']);

	# Look for some connecitons
	foreach ($results as $key => $value) {
		if (preg_match('/^.*,'.$dn.'$/',$key)) {
			echo '<tr class="highlight">';
			printf('<td>%s</td>',$results[$key]['cn']);

			echo '<td class="value">';
			echo '<table class="result_table" border=0>';

			foreach (array(
				'labeledURI',
				'monitorConnectionLocalAddress',
				'monitoredInfo',
				'monitorCounter',
				'monitorOpInitiated',
				'monitorOpCompleted',
				'monitorTimestamp'
				) as $metric) {

				if (isset($results[$key][$metric])) {
					printf('<tr class="%s">',$counter++%2==0?'even':'odd');

					printf('<td class="title">%s</td><td>%s</td>',
						$metric,$results[$key][$metric]);

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
