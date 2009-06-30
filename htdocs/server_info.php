<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/htdocs/server_info.php,v 1.21.2.1 2005/10/09 09:07:21 wurley Exp $

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

require './common.php';

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

if (! $ldapserver->haveAuthInfo())
	pla_error( $lang['not_enough_login_info'] );

# Fetch basic RootDSE attributes using the + and *.
$r = @ldap_read($ldapserver->connect(),'','objectClass=*',array('+','*'));
if (! $r)
	pla_error($lang['could_not_fetch_server_info'],
		  $ldapserver->error(),$ldapserver->errno());

$entry = @ldap_first_entry($ldapserver->connect(),$r);
if (! $entry)
	pla_error($lang['could_not_fetch_server_info'],
		  $ldapserver->error(),$ldapserver->errno());

$attrs = @ldap_get_attributes($ldapserver->connect(),$entry);
$count = @ldap_count_entries($ldapserver->connect(),$r);

/* After fetching the "basic" attributes from the RootDSE, try fetching the
   more advanced ones (from ths list). Add them to the list of attrs to display
   if they weren't already fetched. (this was added as a work-around for OpenLDAP
   on RHEL 3. */
$r2 = @ldap_read($ldapserver->connect(),'','objectClass=*',$root_dse_attributes);
if ($r2) {
	$entry2 = @ldap_first_entry($ldapserver->connect(),$r);
	$attrs2 = @ldap_get_attributes($ldapserver->connect(),$entry);

	for ($i = 0; $i < $attrs2['count']; $i++) {
		$attr = $attrs2[$i];

		if (! isset($attrs[$attr])) {
			$attrs[$attr] = $attrs2[$attr];
			$attrs['count']++;
			$attrs[] = $attr;
		}
	}
}
unset($attrs2,$entry,$entry2);

include './header.php';
?>

<body>
	<h3 class="title"><?php echo $lang['server_info_for'] . htmlspecialchars($ldapserver->name); ?></h3>
	<h3 class="subtitle"><?php echo $lang['server_reports_following']; ?></h3>

<?php if ($count == 0 || $attrs['count'] == 0) { ?>
	<br />
	<br />
	<center><?php echo $lang['nothing_to_report']; ?></center>

<?php
	exit;
}
?>

	<table class="edit_dn">

<?php
for ($i = 0; $i < $attrs['count']; $i++ ) {
	$attr = $attrs[$i];
	$schema_href = sprintf('schema.php?server_id=%s&amp;view=attributes&amp;viewvalue=%s',$ldapserver->server_id,$attr);
?>

		<tr>
			<td class="attr">
				<b>
				<a title="<?php echo sprintf($lang['attr_name_tooltip'],$attr); ?>"
					href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars($attr); ?></a>
				</b>
			</td>
		</tr>

		<tr>
			<td class="val">
				<table class="edit_dn">

<?php
	for ($j = 0; $j < $attrs[$attr]['count']; $j++) {

		$oidtext = '';
		print '<tr>';

		if (preg_match('/^[0-9]+\.[0-9]+/',$attrs[$attr][$j])) {
			printf('<td width=5%%><acronym title="%s"><img src="images/rfc.png"></acronym></td>',
				htmlspecialchars($attrs[$attr][$j]));

			if ($oidtext = support_oid_to_text($attrs[$attr][$j]))
				if (isset($oidtext['ref']))
					printf('<td><acronym title="%s">%s</acronym></td>',$oidtext['ref'],$oidtext['title']);

				else
					printf('<td>%s</td>',$oidtext['title']);

			else
				printf('<td><small>%s</small></td>',$attrs[$attr][$j]);

		} else {
			printf('<td>%s</td>',htmlspecialchars($attrs[$attr][$j]));
		}

		print '</tr>';

		if (isset($oidtext['desc']))
			printf('<tr><td colspan=2><small>%s</small></td></tr>',$oidtext['desc']);
	}
?>

				</table>
			</td>
		</tr>

<?php } ?>

	</table>
</body>
</html>
