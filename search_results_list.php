<?php
// $Header: /cvsroot/phpldapadmin/phpldapadmin/search_results_list.php,v 1.4 2005/03/05 06:27:06 wurley Exp $

/**
 * @package phpLDAPadmin
 */

$friendly_attrs = process_friendly_attr_table();
$entry_id = ldap_first_entry( $ldapserver->connect(), $results );

// Iterate over each entry
$i = 0;
while( $entry_id ) {
	$i++;

	if( $i <= $start_entry ) {
		$entry_id = ldap_next_entry( $ldapserver->connect(), $entry_id );
		continue;
	}

	if( $i >= $end_entry )
		break;

	$dn = ldap_get_dn( $ldapserver->connect(), $entry_id );
	$encoded_dn = rawurlencode( $dn );
	$rdn = get_rdn( $dn ); ?>

<div class="search_result">
	<table>
		<tr>
			<td><img src="images/<?php echo get_icon_use_cache( $ldapserver, $dn ); ?>" /></td>
			<td><a href="edit.php?server_id=<?php echo $server_id; ?>&amp;dn=<?php echo $encoded_dn; ?>"><?php echo htmlspecialchars($rdn); ?></a></td>
		</tr>
	</table>
</div>

<table class="attrs">

	<?php $attrs = ldap_get_attributes( $ldapserver->connect(), $entry_id );
	$attr = ldap_first_attribute( $ldapserver->connect(), $entry_id, $attrs );

	// Always print out the DN in the attribute list
	echo "<tr><td class=\"attr\" valign=\"top\">dn</td>";
	echo "<td>" . htmlspecialchars($dn) . "</td></tr>\n";

	// Iterate over each attribute for this entry
	while( $attr ) {

		if( is_attr_binary( $ldapserver, $attr ) )
			$values = array( "(binary)" );

		else
			$values = ldap_get_values( $ldapserver->connect(), $entry_id, $attr );

		if( isset( $values['count'] ) )
			unset( $values['count'] );

		if( isset( $friendly_attrs[ strtolower( $attr ) ] ) )
			$attr = "<acronym title=\"Alias for $attr\">".htmlspecialchars( $friendly_attrs[ strtolower($attr) ] ) .
		                "</acronym>";

		else
			$attr = htmlspecialchars( $attr ); ?>

	<tr>
		<td class="attr" valign="top"><?php echo $attr; ?></td>
		<td class="val">

		<?php if( is_jpeg_photo( $ldapserver, $attr ) )
			draw_jpeg_photos( $ldapserver, $dn, $attr, false, false, 'align="left"' );

		else
			foreach( $values as $value )
				echo str_replace( ' ', '&nbsp;', htmlspecialchars( $value ) ) . "<br />\n"; ?>

		</td>
	</tr>

		<?php $attr = ldap_next_attribute( $ldapserver->connect(), $entry_id, $attrs );

	} // end while( $attr ) ?>

        </table>

        <?php $entry_id = ldap_next_entry( $ldapserver->connect(), $entry_id );

	// flush every 5th entry (speeds things up a bit)
	if( 0 == $i % 5 )
		flush();

} // end while( $entry_id )
?>
