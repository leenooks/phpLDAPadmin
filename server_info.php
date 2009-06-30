<?php 

/* 
 * server_info.php
 * Fetches and displays all information that it can from the specified server
 * 
 * Variables that come in as GET vars:
 *  - server_id
 */
 
require 'common.php';

$server_id = $_GET['server_id'];
$server_name = $servers[$server_id]['name'];
$ds = pla_ldap_connect( $server_id ) or pla_error( "Could not connect or authenticate to LDAP server" );
$r = @ldap_read( $ds, '', 'objectClass=*', array( '+' ) );
if( ! $r )
	pla_error( "Could not fetch any information from the server" );
$entry = @ldap_first_entry( $ds, $r );
$attrs = @ldap_get_attributes( $ds, $entry );
$count = @ldap_count_entries( $ds, $r );
//echo "<pre>"; print_r( $attrs ); echo "</pre>";
	
include 'header.php';
?>

<h3 class="title">Server info for <?php echo htmlspecialchars( $server_name ); ?></h3>
<h3 class="subtitle">Server reports the following information about itself</h3>

<?php if( $count == 0 || $attrs['count'] == 0 ) { ?>

	<br /><br /><center>This server has nothing to report.</center>
	<?php exit; ?>

<?php } ?>

<table class="edit_dn">
<?php
for( $i=0; $i<$attrs['count']; $i++ ) {
	$attr = $attrs[$i];
	echo "<tr class=\"row" . ($i%2!=0?"1":"2") . "\"><td class=\"attr\"><b>"; 
	echo htmlspecialchars($attr) . "</b></td><td class=\"val\">";
	for( $j=0; $j<$attrs[ $attr ][ 'count' ]; $j++ )
		echo htmlspecialchars( $attrs[ $attr ][ $j ] ) . "<br />\n";
}
?>

</table>
