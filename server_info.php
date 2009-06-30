<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/server_info.php,v 1.11 2004/05/06 04:01:40 uugdave Exp $
 

/* 
 * server_info.php
 * Fetches and displays all information that it can from the specified server
 * 
 * Variables that come in as GET vars:
 *  - server_id
 */
 
require 'common.php';

// The attributes we'll examine when searching the LDAP server's RootDSE
$root_dse_attributes = array( 	'namingContexts', 
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

$server_id = $_GET['server_id'];
$server_name = $servers[$server_id]['name'];
$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );

// Fetch basic RootDSE attributes using the + and *. 
$r = @ldap_read( $ds, '', 'objectClass=*', array( '+', '*' ) );
if( ! $r )
	pla_error( $lang['could_not_fetch_server_info'], ldap_error( $ds ), ldap_errno( $ds )  );
$entry = @ldap_first_entry( $ds, $r );
if( ! $entry )
	pla_error( $lang['could_not_fetch_server_info'], ldap_error( $ds ), ldap_errno( $ds )  );
$attrs = @ldap_get_attributes( $ds, $entry );
$count = @ldap_count_entries( $ds, $r );

// After fetching the "basic" attributes from the RootDSE, try fetching the 
// more advanced ones (from ths list). Add them to the list of attrs to display
// if they weren't already fetched. (this was added as a work-around for OpenLDAP 
// on RHEL 3.
$r2 = @ldap_read( $ds, '', 'objectClass=*', $root_dse_attributes );
if( $r2 ) {
    $entry2 = @ldap_first_entry( $ds, $r );
    $attrs2 = @ldap_get_attributes( $ds, $entry );
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

include 'header.php';
?>

<h3 class="title"><?php echo $lang['server_info_for'] . htmlspecialchars( $server_name ); ?></h3>
<h3 class="subtitle"><?php echo $lang['server_reports_following']; ?></h3>

<?php if( $count == 0 || $attrs['count'] == 0 ) { ?>

	<br /><br /><center><?php echo $lang['nothing_to_report']; ?></center>
	<?php exit; ?>

<?php } ?>

<table class="edit_dn">
<?php
for( $i=0; $i<$attrs['count']; $i++ ) {
	$attr = $attrs[$i];
	$schema_href = "schema.php?server_id=$server_id&amp;view=attributes&viewvalue=$attr";
	?>

	<tr>
		<td class="attr">
			<b>
			<a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ); ?>" 
			   href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars( $attr ); ?>
			</b>
		</td>
    </tr>
    <tr>
		<td class="val">
		<?php for( $j=0; $j<$attrs[ $attr ][ 'count' ]; $j++ )
			echo htmlspecialchars( $attrs[ $attr ][ $j ] ) . "<br />\n"; ?>
		</td>
    </tr>
		
<?php } ?>

</table>
