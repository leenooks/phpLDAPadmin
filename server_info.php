<?php 

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
				'+' );

$server_id = $_GET['server_id'];
$server_name = $servers[$server_id]['name'];
$ds = pla_ldap_connect( $server_id ) or pla_error( $lang['could_not_connect'] );
$r = @ldap_read( $ds, '', 'objectClass=*', $root_dse_attributes );
if( ! $r )
	pla_error( $lang['could_not_fetch_server_info'] );
$entry = @ldap_first_entry( $ds, $r );
$attrs = @ldap_get_attributes( $ds, $entry );
$count = @ldap_count_entries( $ds, $r );
	
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
	$schema_href = "schema.php?server_id=$server_id&amp;view=attributes#" . strtolower($attr);
	?>

	<tr class="row<?php echo ($i%2!=0?"1":"2"); ?>">
		<td class="attr">
			<b>
			<a title="<?php echo sprintf( $lang['attr_name_tooltip'], $attr ); ?>" 
			   href="<?php echo $schema_href; ?>"><?php echo htmlspecialchars( $attr ); ?>
			</b>
		</td>
		<td class="val">
		<?php for( $j=0; $j<$attrs[ $attr ][ 'count' ]; $j++ )
			echo htmlspecialchars( $attrs[ $attr ][ $j ] ) . "<br />\n"; ?>
		</td>
		</tr>
		
<?php
}
?>


</table>
