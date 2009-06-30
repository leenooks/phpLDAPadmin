<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/server_info.php,v 1.18 2005/03/16 09:52:31 wurley Exp $

/**
 * Fetches and displays all information that it can from the specified server
 *
 * Variables that come in as GET vars:
 *  - server_id
 *
 * @package phpLDAPadmin
 */
/**
 */

require './common.php';

// The attributes we'll examine when searching the LDAP server's RootDSE
$root_dse_attributes = array( 'namingContexts',
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

$server_id = (isset($_GET['server_id']) ? $_GET['server_id'] : '');
$ldapserver = new LDAPServer($server_id);
if( ! $ldapserver->haveAuthInfo())
        pla_error( $lang['not_enough_login_info'] );

// Fetch basic RootDSE attributes using the + and *.
$r = @ldap_read( $ldapserver->connect(), '', 'objectClass=*', array( '+', '*' ) );
if( ! $r )
	pla_error( $lang['could_not_fetch_server_info'], ldap_error( $ldapserver->connect() ), ldap_errno( $ldapserver->connect() )  );

$entry = @ldap_first_entry( $ldapserver->connect(), $r );
if( ! $entry )
	pla_error( $lang['could_not_fetch_server_info'], ldap_error( $ldapserver->connect() ), ldap_errno( $ldapserver->connect() )  );

$attrs = @ldap_get_attributes( $ldapserver->connect(), $entry );
$count = @ldap_count_entries( $ldapserver->connect(), $r );

// After fetching the "basic" attributes from the RootDSE, try fetching the
// more advanced ones (from ths list). Add them to the list of attrs to display
// if they weren't already fetched. (this was added as a work-around for OpenLDAP
// on RHEL 3.
$r2 = @ldap_read( $ldapserver->connect(), '', 'objectClass=*', $root_dse_attributes );
if( $r2 ) {
	$entry2 = @ldap_first_entry( $ldapserver->connect(), $r );
	$attrs2 = @ldap_get_attributes( $ldapserver->connect(), $entry );

	for( $i=0; $i<$attrs2['count']; $i++ ) {
		$attr = $attrs2[$i];

		if( ! isset( $attrs[ $attr ] ) ) {
			$attrs[ $attr ] = $attrs2[ $attr ];
			$attrs[ 'count' ]++;
			$attrs[] = $attr;
		}
	}
}
unset( $attrs2, $entry, $entry2 );

include './header.php';
?>

<body>
<h3 class="title"><?php echo $lang['server_info_for'] . htmlspecialchars( $ldapserver->name ); ?></h3>
<h3 class="subtitle"><?php echo $lang['server_reports_following']; ?></h3>

<?php if( $count == 0 || $attrs['count'] == 0 ) { ?>
	<br /><br /><center><?php echo $lang['nothing_to_report']; ?></center>
	<?php exit; ?>
<?php } ?>

<table class="edit_dn">

<?php for( $i=0; $i<$attrs['count']; $i++ ) {
	$attr = $attrs[$i];
	$schema_href = "schema.php?server_id=$server_id&amp;view=attributes&amp;viewvalue=$attr"; ?>

	<tr>
		<td class="attr">
			<b>
			<a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ); ?>"
			   href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars( $attr ); ?></a>
			</b>
		</td>
	</tr>

	<tr>
	<td class="val"><table class="edit_dn">
		<?php for( $j=0; $j<$attrs[ $attr ][ 'count' ]; $j++ ) {
			echo "<tr><td>".htmlspecialchars( $attrs[ $attr ][ $j ] )."</td>";
			if (support_oid_to_text($attrs[ $attr ][ $j ] ))
				echo "<td>".support_oid_to_text($attrs[ $attr ][ $j ] ). "</td></tr>";
		} ?>

		</table></td>
	</tr>

<?php } ?>

</table>
</body>
</html>
