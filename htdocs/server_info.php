<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/server_info.php,v 1.27.2.4 2008/12/12 12:20:22 wurley Exp $

/**
 * Fetches and displays all information that it can from the specified server
 *
 * Variables that come in via common.php
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

if (! $_SESSION[APPCONFIG]->isCommandAvailable('server_info'))
	error(sprintf('%s%s %s',_('This operation is not permitted by the configuration'),_(':'),_('view server information')),'error','index.php');

# The attributes we'll examine when searching the LDAP server's RootDSE
$root_dse_attributes = array(
	'namingContexts',
	'subschemaSubentry',
	'altServer',
	'supportedExtension',
	'supportedControl',
	'supportedSASLMechanisms',
	'supportedLDAPVersion',
	'currentTime',
	'dsServiceName',
	'defaultNamingContext',
	'schemaNamingContext',
	'configurationNamingContext',
	'rootDomainNamingContext',
	'supportedLDAPPolicies',
	'highestCommittedUSN',
	'dnsHostName',
	'ldapServiceName',
	'serverName',
	'supportedCapabilities',
	'changeLog',
	'tlsAvailableCipherSuites',
	'tlsImplementationVersion',
	'supportedSASLMechanisms',
	'dsaVersion',
	'myAccessPoint',
	'dseType',
	'+',
	'*'
	);

# Fetch basic RootDSE attributes using the + and *.
$attrs = $ldapserver->search(null,'','objectClass=*',array('+','*'),'base');
$attrs = array_pop($attrs);

/* After fetching the "basic" attributes from the RootDSE, try fetching the
   more advanced ones (from ths list). Add them to the list of attrs to display
   if they weren't already fetched. (this was added as a work-around for OpenLDAP
   on RHEL 3. */
$attrs2 = $ldapserver->search(null,'','objectClass=*',$root_dse_attributes,'base');
$attrs2 = array_pop($attrs2);

if (is_array($attrs2))
	foreach ($attrs2 as $attr => $values)
		if (! isset($attrs[$attr]))
			$attrs[$attr] = $attrs2[$attr];

printf('<h3 class="title">%s%s</h3>',_('Server info for: '),htmlspecialchars($ldapserver->name));
printf('<h3 class="subtitle">%s</h3>',_('Server reports the following information about itself'));

if (count($attrs) == 0) {
	echo '<br /><br />';
	printf('<center>%s</center>',_('This server has nothing to report.'));
	return;
}

echo '<table class="result" border=0>';
foreach ($attrs as $attr => $values) {
	if ($attr == 'dn')
		continue;

	$schema_href = sprintf('cmd.php?cmd=schema&amp;server_id=%s&amp;view=attributes&amp;viewvalue=%s',$ldapserver->server_id,$attr);

	echo '<tr class="list_item"><td class="heading" rowspan=2>';
	printf('<a title="'._('Click to view the schema definition for attribute type \'%s\'').'" href="%s">%s</a>',
		$attr,$schema_href,htmlspecialchars($attr));
	echo '</td></tr>';

	echo '<tr class="list_item"><td class="blank">&nbsp;</td><td class="value">';
	echo '<table class="result" border=0>';

	if (is_array($values))
		foreach ($values as $value) {

		$oidtext = '';
		print '<tr>';

		if (preg_match('/^[0-9]+\.[0-9]+/',$value)) {
			printf('<td width=5%%><img src="%s/rfc.png" title="%s" alt="%s" /></td>',
			       IMGDIR,htmlspecialchars($value), htmlspecialchars($value));

			if ($oidtext = support_oid_to_text($value))
				if (isset($oidtext['ref']))
					printf('<td><acronym title="%s">%s</acronym></td>',$oidtext['ref'],$oidtext['title']);
				else
					printf('<td>%s</td>',$oidtext['title']);

			else
			if (strlen($value) > 0)
					printf('<td><small>%s</small></td>',$value);

		} else {
			printf('<td>%s</td>',htmlspecialchars($value));
		}

		print '</tr>';

		if (isset($oidtext['desc']) && trim($oidtext['desc']))
			printf('<tr><td colspan=2><small>%s</small></td></tr>',$oidtext['desc']);
		}

	else
		printf('<tr><td>%s&nbsp;</td></tr>',htmlspecialchars($values));

	echo '</table>';
	echo '</td></tr>';
}
echo '</table>';
?>
